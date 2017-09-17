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
            new Fbns\Auth($this->_instagram),
            $this->_instagram->device,
            $this->_loop,
            $this->_logger
        );
        $fbns->on('token', function ($token) {
            // Register the received token.
            try {
                $this->_instagram->push->register('mqtt', $token);
            } catch (\Exception $e) {
                $this->emit('error', $e);
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
