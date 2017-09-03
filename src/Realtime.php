<?php

namespace InstagramAPI;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use InstagramAPI\React\Connector;
use InstagramAPI\Realtime\Mqtt\Auth;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;

/**
 * The following events are emitted:
 *  - live-started - New live broadcast has been started.
 *  - live-stopped - An existing live broadcast has been stopped.
 *  - direct-story-created - New direct story has been created.
 *  - direct-story-updated - New item has been created in direct story.
 *  - direct-story-screenshot - Someone has taken a screenshot of your direct story.
 *  - direct-story-action - Direct story badge has been updated with some action.
 *  - thread-created - New thread has been created.
 *  - thread-updated - An existing thread has been updated.
 *  - thread-notify - Someone has created ActionLog item in thread.
 *  - thread-seen - Someone has updated their last seen position.
 *  - thread-activity - Someone has created an activity (like start/stop typing) in thread.
 *  - thread-item-created - New item has been created in thread.
 *  - thread-item-updated - An existing item has been updated in thread.
 *  - thread-item-removed - An existing item has been removed from thread.
 *  - client-context-ack - Acknowledgment for client_context has been received.
 *  - unseen-count-update - Unseen count indicator has been updated.
 *  - error - An event of severity "error" occurred.
 */
class Realtime implements EventEmitterInterface
{
    use EventEmitterTrait;

    /** @var Instagram */
    protected $_instagram;

    /** @var LoopInterface */
    protected $_loop;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var Realtime\Mqtt */
    protected $_client;

    /**
     * Constructor.
     *
     * @param Instagram            $instagram
     * @param LoopInterface        $loop
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Instagram $instagram,
        LoopInterface $loop,
        LoggerInterface $logger = null)
    {
        $this->_instagram = $instagram;
        $this->_loop = $loop;
        $this->_logger = $logger;
        if ($this->_logger === null) {
            $this->_logger = new NullLogger();
        }

        $this->_client = $this->_getClient();
    }

    /**
     * Create a new MQTT client.
     *
     * @return Realtime\Mqtt
     */
    protected function _getClient()
    {
        return new Realtime\Mqtt(
            $this,
            new Connector($this->_instagram, $this->_loop),
            new Auth($this->_instagram),
            $this->_instagram->device,
            $this->_instagram->experiments,
            $this->_loop,
            $this->_logger
        );
    }

    /**
     * Starts underlying client.
     */
    public function start()
    {
        $this->_client->start();
    }

    /**
     * Stops underlying client.
     */
    public function stop()
    {
        $this->_client->stop();
    }

    /**
     * @param array $command
     *
     * @return bool
     */
    protected function _sendCommand(
        array $command)
    {
        $command = static::jsonEncode($command);

        return $this->_client->sendCommand($command);
    }

    /**
     * Marks thread item as seen.
     *
     * @param string $threadId
     * @param string $threadItemId
     *
     * @return bool
     */
    public function markDirectItemSeen(
        $threadId,
        $threadItemId)
    {
        return $this->_sendCommand([
            'thread_id' => $threadId,
            'item_id'   => $threadItemId,
            'action'    => 'mark_seen',
        ]);
    }

    /**
     * Indicate activity in thread.
     *
     * @param string $threadId
     * @param bool   $activityFlag
     *
     * @return bool|string Client context or false if sending is unavailable.
     */
    public function indicateActivityInDirectThread(
        $threadId,
        $activityFlag)
    {
        $context = Signatures::generateUUID(true);
        $result = $this->_sendCommand([
            'thread_id'       => $threadId,
            'client_context'  => $context,
            'activity_status' => $activityFlag ? '1' : '0',
            'action'          => 'indicate_activity',
        ]);

        return $result ? $context : false;
    }

    /**
     * Common method for all direct messages.
     *
     * @param array $options
     *
     * @return bool|string Client context or false if sending is unavailable.
     */
    protected function _sendItemToDirect(
        array $options)
    {
        // Init command.
        $command = [
            'action' => 'send_item',
        ];
        // Handle client_context.
        if (!isset($options['client_context'])) {
            $command['client_context'] = Signatures::generateUUID(true);
        } elseif (!Signatures::isValidUUID($options['client_context'])) {
            return false;
        } else {
            $command['client_context'] = $options['client_context'];
        }
        // Handle thread_id.
        if (!isset($options['thread_id'])) {
            return false;
        } elseif (!ctype_digit($options['thread_id']) && (!is_int($options['thread_id']) || $options['thread_id'] < 0)) {
            return false;
        } else {
            $command['thread_id'] = $options['thread_id'];
        }
        // Handle item_type specifics.
        if (!isset($options['item_type'])) {
            return false;
        }
        switch ($options['item_type']) {
            case 'text':
                if (!isset($options['text'])) {
                    return false;
                }
                $command['text'] = $options['text'];
                break;
            case 'like':
                // Nothing here.
                break;
            case 'reaction':
                // Handle item_id.
                if (!isset($options['item_id'])) {
                    return false;
                } elseif (!ctype_digit($options['item_id']) && (!is_int($options['item_id']) || $options['item_id'] < 0)) {
                    return false;
                } else {
                    $command['item_id'] = $options['item_id'];
                    $command['node_type'] = 'item';
                }
                // Handle reaction_type.
                if (!isset($options['reaction_type'])) {
                    return false;
                } elseif (!in_array($options['reaction_type'], ['like'], true)) {
                    return false;
                } else {
                    $command['reaction_type'] = $options['reaction_type'];
                }
                // Handle reaction_status.
                if (!isset($options['reaction_status'])) {
                    return false;
                } elseif (!in_array($options['reaction_status'], ['created', 'deleted'], true)) {
                    return false;
                } else {
                    $command['reaction_status'] = $options['reaction_status'];
                }
                break;
            default:
                return false;
        }
        $command['item_type'] = $options['item_type'];
        // Reorder command to simplify comparing against commands created by an application.
        $command = $this->reorderFieldsByWeight($command, $this->getSendItemWeights());

        return $this->_sendCommand($command) ? $command['client_context'] : false;
    }

    /**
     * Sends text message to a given direct thread.
     *
     * @param string $threadId Thread ID.
     * @param string $message  Text message.
     * @param array  $options  An associative array of optional parameters, including:
     *                         "client_context" - predefined UUID used to prevent double-posting;
     *
     * @return bool|string Client context or false if sending is unavailable.
     */
    public function sendTextToDirect(
        $threadId,
        $message,
        array $options = [])
    {
        return $this->_sendItemToDirect(array_merge($options, [
            'thread_id' => $threadId,
            'item_type' => 'text',
            'text'      => $message,
        ]));
    }

    /**
     * Sends like to a given direct thread.
     *
     * @param string $threadId Thread ID.
     * @param array  $options  An associative array of optional parameters, including:
     *                         "client_context" - predefined UUID used to prevent double-posting;
     *
     * @return bool|string Client context or false if sending is unavailable.
     */
    public function sendLikeToDirect(
        $threadId,
        array $options = [])
    {
        return $this->_sendItemToDirect(array_merge($options, [
            'thread_id' => $threadId,
            'item_type' => 'like',
        ]));
    }

    /**
     * Sends reaction to a given direct thread item.
     *
     * @param string $threadId     Thread ID.
     * @param string $threadItemId Thread ID.
     * @param string $reactionType One of: "like".
     * @param array  $options      An associative array of optional parameters, including:
     *                             "client_context" - predefined UUID used to prevent double-posting;
     *
     * @return bool|string Client context or false if sending is unavailable.
     */
    public function sendReactionToDirect(
        $threadId,
        $threadItemId,
        $reactionType,
        array $options = [])
    {
        return $this->_sendItemToDirect(array_merge($options, [
            'thread_id'       => $threadId,
            'item_type'       => 'reaction',
            'reaction_status' => 'created',
            'reaction_type'   => $reactionType,
            'item_id'         => $threadItemId,
        ]));
    }

    /**
     * Removes reaction to a given direct thread item.
     *
     * @param string $threadId     Thread ID.
     * @param string $threadItemId Thread ID.
     * @param string $reactionType One of: "like".
     * @param array  $options      An associative array of optional parameters, including:
     *                             "client_context" - predefined UUID used to prevent double-posting;
     *
     * @return bool|string Client context or false if sending is unavailable.
     */
    public function deleteReactionFromDirect(
        $threadId,
        $threadItemId,
        $reactionType,
        array $options = [])
    {
        return $this->_sendItemToDirect(array_merge($options, [
            'thread_id'       => $threadId,
            'item_type'       => 'reaction',
            'reaction_status' => 'deleted',
            'reaction_type'   => $reactionType,
            'item_id'         => $threadItemId,
        ]));
    }

    /**
     * Proxy for json_encode() with some necessary flags.
     *
     * @param mixed $data
     *
     * @return string
     */
    public static function jsonEncode(
        $data)
    {
        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Reorders an array of fields by weights to simplify debugging.
     *
     * @param array $fields
     * @param array $weights
     *
     * @return array
     */
    public function reorderFieldsByWeight(
        array $fields,
        array $weights)
    {
        uksort($fields, function ($a, $b) use ($weights) {
            $a = isset($weights[$a]) ? $weights[$a] : PHP_INT_MAX;
            $b = isset($weights[$b]) ? $weights[$b] : PHP_INT_MAX;
            if ($a < $b) {
                return -1;
            } elseif ($a > $b) {
                return 1;
            } else {
                return 0;
            }
        });

        return $fields;
    }

    /**
     * Returns an array of weights for ordering fields.
     *
     * @return array
     */
    public function getSendItemWeights()
    {
        return [
            'thread_id'       => 10,
            'item_type'       => 15,
            'text'            => 20,
            'client_context'  => 25,
            'activity_status' => 30,
            'reaction_type'   => 35,
            'reaction_status' => 40,
            'item_id'         => 45,
            'node_type'       => 50,
            'action'          => 55,
            'profile_user_id' => 60,
            'hashtag'         => 65,
            'venue_id'        => 70,
            'media_id'        => 75,
        ];
    }

    /**
     * Update Iris sequence ID.
     *
     * @param int $sequenceId
     */
    public function updateSequenceId(
        $sequenceId)
    {
        $this->_client->updateSequenceId($sequenceId);
    }
}
