<?php

namespace InstagramAPI;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use InstagramAPI\Push\Fbns;
use InstagramAPI\Push\Notification;
use InstagramAPI\React\Connector;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;

/**
 * The following events are emitted:
 *  - incoming - New PUSH notification has been received.
 *  - like - Someone has liked your media.
 *  - comment - Someone has commented on your media.
 *  - direct_v2_message - Someone has messaged you.
 *  - usertag - You have been tagged in some photo.
 *  - mentioned_comment - You have been mentioned in some comment.
 *  - new_follower - Someone has started following you.
 *  - live_broadcast - Someone you know has started live broadcast.
 *  - live_broadcast_revoke - Someone you know has stopped live broadcast.
 *  - follow_request_approved - Your follow request has been approved.
 *  - comment_like - Your comment has been liked by someone.
 *  - private_user_follow_request - Someone wants to follow you.
 *  - post - Someone you know has posted something.
 *  - comment_on_tag - Someone has commented on photo you are tagged in.
 *  - ...
 *  - error - An event of severity "error" occurred.
 */
class Push implements EventEmitterInterface
{
    use EventEmitterTrait;

    /** @var Instagram */
    protected $_instagram;

    /** @var LoopInterface */
    protected $_loop;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var Fbns */
    protected $_fbns;

    /** @var Fbns\Auth */
    protected $_fbnsAuth;

    /**
     * Push constructor.
     *
     * @param LoopInterface        $loop
     * @param Instagram            $instagram
     * @param LoggerInterface|null $logger
     *
     * @throws \RuntimeException
     */
    public function __construct(
        LoopInterface $loop,
        Instagram $instagram,
        LoggerInterface $logger = null)
    {
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('The Push client can only run from the command line.');
        }

        $this->_instagram = $instagram;
        $this->_loop = $loop;
        $this->_logger = $logger;
        if ($this->_logger === null) {
            $this->_logger = new NullLogger();
        }

        $this->_fbnsAuth = new Fbns\Auth($this->_instagram);
        $this->_fbns = $this->_getFbns();
    }

    /**
     * Incoming notification callback.
     *
     * @param Notification $notification
     */
    protected function _onPush(
        Notification $notification)
    {
        $collapseKey = $notification->getCollapseKey();
        $this->_logger->info(sprintf('Received a push with collapse key "%s"', $collapseKey), [(string) $notification]);
        $this->emit('incoming', [$notification]);
        if (!empty($collapseKey)) {
            $this->emit($collapseKey, [$notification]);
        }
    }

    /**
     * Create a new FBNS receiver.
     *
     * @return Fbns
     */
    protected function _getFbns()
    {
        $fbns = new Fbns(
            $this,
            new Connector($this->_instagram, $this->_loop),
            $this->_fbnsAuth,
            $this->_instagram->device,
            $this->_loop,
            $this->_logger
        );
        $fbns->on('fbns_auth', function ($authJson) {
            try {
                $this->_fbnsAuth->update($authJson);
            } catch (\Exception $e) {
                $this->_logger->error(sprintf('Failed to update FBNS auth: %s', $e->getMessage()), [$authJson]);
            }
        });
        $fbns->on('fbns_token', function ($token) {
            // Refresh the "last token activity" timestamp.
            // The age of this timestamp helps us detect when the user
            // has stopped using the Push features due to inactivity.
            try {
                $this->_instagram->settings->set('last_fbns_token', time());
            } catch (\Exception $e) {
                $this->_logger->error(sprintf('Failed to write FBNS token timestamp: %s', $e->getMessage()));
            }
            // Read our old token. If an identical value exists, then we know
            // that we've already registered that token during this session.
            try {
                $oldToken = $this->_instagram->settings->get('fbns_token');
                // Do nothing when the new token is equal to the old one.
                if ($token === $oldToken) {
                    return;
                }
            } catch (\Exception $e) {
                $this->_logger->error(sprintf('Failed to read FBNS token: %s', $e->getMessage()));
            }
            // Register the new token.
            try {
                $this->_instagram->push->register('mqtt', $token);
            } catch (\Exception $e) {
                $this->emit('error', $e);
            }
            // Save the newly received token to the storage.
            // NOTE: We save it even if the registration failed, since we now
            // got it from the server and assume they've given us a good one.
            // However, it'll always be re-validated during the general login()
            // flow, and will be cleared there if it fails to register there.
            try {
                $this->_instagram->settings->set('fbns_token', $token);
            } catch (\Exception $e) {
                $this->_logger->error(sprintf('Failed to update FBNS token: %s', $e->getMessage()), [$token]);
            }
        });
        $fbns->on('push', function (Notification $notification) {
            $this->_onPush($notification);
        });

        return $fbns;
    }

    /**
     * Start Push receiver.
     */
    public function start()
    {
        $this->_fbns->start();
    }

    /**
     * Stop Push receiver.
     */
    public function stop()
    {
        $this->_fbns->stop();
    }
}
