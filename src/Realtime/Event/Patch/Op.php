<?php

namespace InstagramAPI\Realtime\Event\Patch;

use Evenement\EventEmitterInterface;
use InstagramAPI\AutoPropertyMapper;
use InstagramAPI\Client as HttpClient;
use InstagramAPI\Realtime\Event\Payload as EventPayload;
use InstagramAPI\Response\Model\ActionBadge;
use InstagramAPI\Response\Model\DirectInbox;
use InstagramAPI\Response\Model\DirectSeenItemPayload;
use InstagramAPI\Response\Model\DirectThread;
use InstagramAPI\Response\Model\DirectThreadItem;
use InstagramAPI\Response\Model\DirectThreadLastSeenAt;
use Psr\Log\LoggerInterface;

/**
 * Op.
 *
 * @method mixed getDoublePublish()
 * @method mixed getOp()
 * @method mixed getPath()
 * @method mixed getTs()
 * @method mixed getValue()
 * @method bool isDoublePublish()
 * @method bool isOp()
 * @method bool isPath()
 * @method bool isTs()
 * @method bool isValue()
 * @method $this setDoublePublish(mixed $value)
 * @method $this setOp(mixed $value)
 * @method $this setPath(mixed $value)
 * @method $this setTs(mixed $value)
 * @method $this setValue(mixed $value)
 * @method $this unsetDoublePublish()
 * @method $this unsetOp()
 * @method $this unsetPath()
 * @method $this unsetTs()
 * @method $this unsetValue()
 */
class Op extends AutoPropertyMapper
{
    const ADD = 'add';
    const REMOVE = 'remove';
    const REPLACE = 'replace';
    const NOTIFY = 'notify';

    const JSON_PROPERTY_MAP = [
        'op'            => '',
        'path'          => '',
        'value'         => '',
        'ts'            => '',
        'doublePublish' => '',
    ];

    /** @var EventEmitterInterface */
    protected $_target;

    /** @var LoggerInterface */
    protected $_logger;

    /**
     * Checks if $path starts with specified substring.
     *
     * @param string $path
     * @param string $string
     *
     * @return bool
     */
    protected function _isPathStartsWith(
        $path,
        $string)
    {
        return strncmp($path, $string, strlen($string)) === 0;
    }

    /**
     * Checks if $path ends with specified substring.
     *
     * @param string $path
     * @param string $string
     *
     * @return bool
     */
    protected function _isPathEndsWith(
        $path,
        $string)
    {
        $length = strlen($string);

        return substr_compare($path, $string, strlen($path) - $length, $length) === 0;
    }

    /**
     * Checks if target has at least one listener for specific event.
     *
     * @param string $event
     *
     * @return bool
     */
    protected function _hasListeners(
        $event)
    {
        return (bool) count($this->_target->listeners($event));
    }

    /**
     * Handler for thread item creation/modification.
     */
    protected function _upsertThreadItem()
    {
        $hasListeners = false;
        $op = $this->_getProperty('op');
        switch ($op) {
            case self::ADD:
                $hasListeners = $this->_hasListeners('thread-item-created');
                break;
            case self::REPLACE:
                $hasListeners = $this->_hasListeners('thread-item-updated');
                break;
            default:
                $this->_logger->warning(sprintf('Unsupported thread item op: "%s"', $op));
        }
        if (!$hasListeners) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread item regexp', $path));

            return;
        }
        list($path, $threadId, $threadItemId) = $matches; // NOTE: Changes $path.
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread item JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectThreadItem $threadItem */
        $threadItem = new DirectThreadItem($json);
        $this->_target->emit(
            $op === self::ADD ? 'thread-item-created' : 'thread-item-updated',
            [$threadId, $threadItemId, $threadItem]
        );
    }

    /**
     * Handler for thread creation/modification.
     */
    protected function _upsertThread()
    {
        $hasListeners = false;
        $op = $this->_getProperty('op');
        switch ($op) {
            case self::ADD:
                $hasListeners = $this->_hasListeners('thread-created');
                break;
            case self::REPLACE:
                $hasListeners = $this->_hasListeners('thread-updated');
                break;
            default:
                $this->_logger->warning(sprintf('Unsupported thread op: "%s"', $op));
        }
        if (!$hasListeners) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/inbox/threads/(.+)$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread regexp', $path));

            return;
        }
        list($path, $threadId) = $matches; // NOTE: Changes $path.
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectThread $thread */
        $thread = new DirectThread($json);
        $this->_target->emit(
            $op === self::ADD ? 'thread-created' : 'thread-updated',
            [$threadId, $thread]
        );
    }

    /**
     * Handler for live broadcast creation/removal.
     */
    protected function _handleLiveBroadcast()
    {
        $hasListeners = false;
        $op = $this->_getProperty('op');
        switch ($op) {
            case self::ADD:
                $hasListeners = $this->_hasListeners('live-started');
                break;
            case self::REMOVE:
                $hasListeners = $this->_hasListeners('live-stopped');
                break;
            default:
                $this->_logger->warning(sprintf('Unsupported live broadcast op: "%s"', $op));
        }
        if (!$hasListeners) {
            return;
        }

        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode live broadcast JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var EventPayload\Live $livePayload */
        $livePayload = new EventPayload\Live($json);
        $this->_target->emit(
            $op === self::ADD ? 'live-started' : 'live-stopped',
            [$livePayload]
        );
    }

    /**
     * Handler for thread activity indicator.
     */
    protected function _upsertThreadActivity()
    {
        if (!$this->_hasListeners('thread-activity')) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/threads/([^/]+)/activity_indicator_id/(.+)$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread activity regexp', $path));

            return;
        }
        list($path, $threadId, $indicatorId) = $matches; // NOTE: Changes $path.
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread activity JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var EventPayload\Activity $activity */
        $activity = new EventPayload\Activity($json);
        $this->_target->emit('thread-activity', [$threadId, $activity]);
    }

    /**
     * Handler for story update.
     */
    protected function _updateDirectStory()
    {
        if (!$this->_hasListeners('direct-story-updated')) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/visual_threads/([^/]+)/items/(.+)$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match story item regexp', $path));

            return;
        }
        list($path, $threadId, $threadItemId) = $matches; // NOTE: Changes $path.
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread item JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectThreadItem $threadItem */
        $threadItem = new DirectThreadItem($json);
        $this->_target->emit('direct-story-updated', [$threadId, $threadItemId, $threadItem]);
    }

    /**
     * Handler for ADD op.
     */
    protected function _handleAdd()
    {
        $handled = false;
        $path = $this->_getProperty('path');
        if ($this->_isPathStartsWith($path, '/direct_v2/threads')) {
            if (strpos($path, 'activity_indicator_id') === false) {
                $this->_upsertThreadItem();
                $handled = true;
            } else {
                $this->_upsertThreadActivity();
                $handled = true;
            }
        } elseif ($this->_isPathStartsWith($path, '/direct_v2/inbox/threads')) {
            $this->_upsertThread();
            $handled = true;
        } elseif ($this->_isPathStartsWith($path, '/broadcast')) {
            $this->_handleLiveBroadcast();
            $handled = true;
        } elseif ($this->_isPathStartsWith($path, '/direct_v2/visual_threads')) {
            $this->_updateDirectStory();
            $handled = true;
        }

        if (!$handled) {
            $this->_logger->warning(sprintf('Unsupported ADD path "%s"', $path));
        }
    }

    /**
     * Handler for unseen count.
     */
    protected function _updateUnseenCount()
    {
        if (!$this->_hasListeners('unseen-count-update')) {
            return;
        }

        $payload = new DirectSeenItemPayload([
            'count'     => (int) $this->_getProperty('value'),
            'timestamp' => $this->_getProperty('ts'),
        ]);
        $this->_target->emit('unseen-count-update', [$payload]);
    }

    /**
     * Handler for thread seen indicator.
     */
    protected function _updateSeen()
    {
        if (!$this->_hasListeners('thread-seen')) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/threads/([^/]+)/participants/([^/]+)/has_seen$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread seen regexp', $path));

            return;
        }
        list($path, $threadId, $userId) = $matches; // NOTE: Changes $path.
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread seen JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectThreadLastSeenAt $lastSeenAt */
        $lastSeenAt = new DirectThreadLastSeenAt($json);
        $this->_target->emit('thread-seen', [$threadId, $userId, $lastSeenAt]);
    }

    /**
     * Handler for screenshot notification.
     */
    protected function _notifyDirectStoryScreenshot()
    {
        if (!$this->_hasListeners('direct-story-screenshot')) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/visual_thread/([^/]+)/screenshot$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread screenshot regexp', $path));

            return;
        }
        list($path, $threadId) = $matches; // NOTE: Changes $path.
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var EventPayload\Screenshot $screenshot */
        $screenshot = new EventPayload\Screenshot($json);
        $this->_target->emit('direct-story-screenshot', [$threadId, $screenshot]);
    }

    /**
     * Handler for direct story creation.
     */
    protected function _createDirectStory()
    {
        if (!$this->_hasListeners('direct-story-created')) {
            return;
        }

        $path = $this->_getProperty('path');
        if ($path !== '/direct_v2/visual_thread/create') {
            $this->_logger->warning(sprintf('Path %s does not match story create path', $path));

            return;
        }
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode inbox JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectInbox $inbox */
        $inbox = new DirectInbox($json);
        $allThreads = $inbox->getThreads();
        if (!isset($allThreads) || !count($allThreads)) {
            return;
        }
        /** @var DirectThread $thread */
        $thread = reset($allThreads); // Get first thread.
        $this->_target->emit('direct-story-created', [$thread]);
    }

    /**
     * Handler for story action.
     */
    protected function _directStoryAction()
    {
        if (!$this->_hasListeners('direct-story-action')) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/visual_action_badge/(.+)$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match story action regexp', $path));

            return;
        }
        list($path, $threadId) = $matches; // NOTE: Changes $path.
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode story action JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var ActionBadge $storyAction */
        $storyAction = new ActionBadge($json);
        $this->_target->emit('direct-story-action', [$threadId, $storyAction]);
    }

    /**
     * Handler for REPLACE op.
     */
    protected function _handleReplace()
    {
        $handled = false;
        $path = $this->_getProperty('path');
        if ($this->_isPathStartsWith($path, '/direct_v2/threads')) {
            if ($this->_isPathEndsWith($path, 'has_seen')) {
                $this->_updateSeen();
                $handled = true;
            } else {
                $this->_upsertThreadItem();
                $handled = true;
            }
        } elseif ($this->_isPathStartsWith($path, '/direct_v2/inbox/threads')) {
            $this->_upsertThread();
            $handled = true;
        } elseif ($this->_isPathStartsWith($path, '/direct_v2/inbox') || $this->_isPathStartsWith($path, '/direct_v2/visual_inbox')) {
            if ($this->_isPathEndsWith($path, 'unseen_count')) {
                $this->_updateUnseenCount();
                $handled = true;
            }
        } elseif ($this->_isPathStartsWith($path, '/direct_v2/visual_action_badge')) {
            $this->_directStoryAction();
            $handled = true;
        } elseif ($this->_isPathStartsWith($path, '/direct_v2/visual_thread')) {
            if ($this->_isPathEndsWith($path, 'screenshot')) {
                $this->_notifyDirectStoryScreenshot();
                $handled = true;
            } elseif ($this->_isPathEndsWith($path, 'create')) {
                $this->_createDirectStory();
                $handled = true;
            }
        }

        if (!$handled) {
            $this->_logger->warning(sprintf('Unsupported REPLACE path "%s"', $path));
        }
    }

    /**
     * Handler for thread item removal.
     */
    protected function _removeThreadItem()
    {
        if (!$this->_hasListeners('thread-item-removed')) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread item regexp', $path));

            return;
        }
        list($path, $threadId, $threadItemId) = $matches; // NOTE: Changes $path.
        $this->_target->emit('thread-item-removed', [$threadId, $threadItemId]);
    }

    /**
     * Handler for REMOVE op.
     */
    protected function _handleRemove()
    {
        $handled = false;
        $path = $this->_getProperty('path');
        if ($this->_isPathStartsWith($path, '/direct_v2')) {
            $this->_removeThreadItem();
            $handled = true;
        } elseif ($this->_isPathStartsWith($path, '/broadcast')) {
            $this->_handleLiveBroadcast();
            $handled = true;
        }

        if (!$handled) {
            $this->_logger->warning(sprintf('Unsupported REMOVE path "%s"', $path));
        }
    }

    /**
     * Handler for thread notify.
     */
    protected function _notifyThread()
    {
        if (!$this->_hasListeners('thread-notify')) {
            return;
        }

        $path = $this->_getProperty('path');
        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread item regexp', $path));

            return;
        }
        list($path, $threadId, $threadItemId) = $matches; // NOTE: Changes $path.
        $json = HttpClient::api_body_decode($this->_getProperty('value'));
        if (!is_array($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread item notify JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var EventPayload\Notify $notifyPayload */
        $notifyPayload = new EventPayload\Notify($json);
        $this->_target->emit('thread-notify', [$threadId, $threadItemId, $notifyPayload]);
    }

    /**
     * Handler for NOTIFY op.
     */
    protected function _handleNotify()
    {
        $handled = false;
        $path = $this->_getProperty('path');
        if ($this->_isPathStartsWith($path, '/direct_v2/threads')) {
            $this->_notifyThread();
            $handled = true;
        }

        if (!$handled) {
            $this->_logger->warning(sprintf('Unsupported NOTIFY path "%s"', $path));
        }
    }

    /**
     * Event handler.
     *
     * @param EventEmitterInterface $target
     * @param LoggerInterface       $logger
     */
    public function handle(
        EventEmitterInterface $target,
        LoggerInterface $logger)
    {
        $this->_target = $target;
        $this->_logger = $logger;
        $op = $this->_getProperty('op');
        switch ($op) {
            case self::ADD:
                $this->_handleAdd();
                break;
            case self::REPLACE:
                $this->_handleReplace();
                break;
            case self::REMOVE:
                $this->_handleRemove();
                break;
            case self::NOTIFY:
                $this->_handleNotify();
                break;
            default:
                $this->_logger->warning(sprintf('Unknown patch op "%s"', $op));
        }
    }
}
