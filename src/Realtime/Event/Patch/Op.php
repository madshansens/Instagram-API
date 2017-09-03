<?php

namespace InstagramAPI\Realtime\Event\Patch;

use Evenement\EventEmitterInterface;
use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\Client as HttpClient;
use InstagramAPI\Realtime\Event\Payload as EventPayload;
use InstagramAPI\Response\Model\ActionBadge;
use InstagramAPI\Response\Model\DirectInbox;
use InstagramAPI\Response\Model\DirectSeenItemPayload;
use InstagramAPI\Response\Model\DirectThread;
use InstagramAPI\Response\Model\DirectThreadItem;
use InstagramAPI\Response\Model\DirectThreadLastSeenAt;
use JsonMapper;
use Psr\Log\LoggerInterface;

/**
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
 * @method setDoublePublish(mixed $value)
 * @method setOp(mixed $value)
 * @method setPath(mixed $value)
 * @method setTs(mixed $value)
 * @method setValue(mixed $value)
 */
class Op extends AutoPropertyHandler
{
    const ADD = 'add';
    const REMOVE = 'remove';
    const REPLACE = 'replace';
    const NOTIFY = 'notify';

    public $op;
    public $path;
    public $value;
    public $ts;
    public $doublePublish;

    /** @var EventEmitterInterface */
    protected $_target;

    /** @var JsonMapper */
    protected $_jsonMapper;

    /** @var LoggerInterface */
    protected $_logger;

    /**
     * Checks if $path starts with specified substring.
     *
     * @param string $string
     *
     * @return bool
     */
    protected function _isPathStartsWith(
        $string)
    {
        return strncmp($this->path, $string, strlen($string)) === 0;
    }

    /**
     * Checks if $path ends with specified substring.
     *
     * @param string $string
     *
     * @return bool
     */
    protected function _isPathEndsWith(
        $string)
    {
        $length = strlen($string);

        return substr_compare($this->path, $string, strlen($this->path) - $length, $length) === 0;
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
        switch ($this->op) {
            case self::ADD:
                $hasListeners = $this->_hasListeners('thread-item-created');
                break;
            case self::REPLACE:
                $hasListeners = $this->_hasListeners('thread-item-updated');
                break;
            default:
                $this->_logger->warning(sprintf('Unsupported thread item op: "%s"', $this->op));
        }
        if (!$hasListeners) {
            return;
        }

        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread item regexp', $this->path));

            return;
        }
        list($path, $threadId, $threadItemId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread item JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectThreadItem $threadItem */
        $threadItem = $this->_jsonMapper->map($json, new DirectThreadItem());
        $this->_target->emit(
            $this->op === self::ADD ? 'thread-item-created' : 'thread-item-updated',
            [$threadId, $threadItemId, $threadItem]
        );
    }

    /**
     * Handler for thread creation/modification.
     */
    protected function _upsertThread()
    {
        $hasListeners = false;
        switch ($this->op) {
            case self::ADD:
                $hasListeners = $this->_hasListeners('thread-created');
                break;
            case self::REPLACE:
                $hasListeners = $this->_hasListeners('thread-updated');
                break;
            default:
                $this->_logger->warning(sprintf('Unsupported thread op: "%s"', $this->op));
        }
        if (!$hasListeners) {
            return;
        }

        if (!preg_match('#^/direct_v2/inbox/threads/(.+)$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread regexp', $this->path));

            return;
        }
        list($path, $threadId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectThread $thread */
        $thread = $this->_jsonMapper->map($json, new DirectThread());
        $this->_target->emit(
            $this->op === self::ADD ? 'thread-created' : 'thread-updated',
            [$threadId, $thread]
        );
    }

    /**
     * Handler for live broadcast creation/removal.
     */
    protected function _handleLiveBroadcast()
    {
        $hasListeners = false;
        switch ($this->op) {
            case self::ADD:
                $hasListeners = $this->_hasListeners('live-started');
                break;
            case self::REMOVE:
                $hasListeners = $this->_hasListeners('live-stopped');
                break;
            default:
                $this->_logger->warning(sprintf('Unsupported live broadcast op: "%s"', $this->op));
        }
        if (!$hasListeners) {
            return;
        }

        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode live broadcast JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var EventPayload\Live $livePayload */
        $livePayload = $this->_jsonMapper->map($json, new EventPayload\Live());
        $this->_target->emit(
            $this->op === self::ADD ? 'live-started' : 'live-stopped',
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

        if (!preg_match('#^/direct_v2/threads/([^/]+)/activity_indicator_id/(.+)$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread activity regexp', $this->path));

            return;
        }
        list($path, $threadId, $indicatorId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread activity JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var EventPayload\Activity $activity */
        $activity = $this->_jsonMapper->map($json, new EventPayload\Activity());
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

        if (!preg_match('#^/direct_v2/visual_threads/([^/]+)/items/(.+)$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match story item regexp', $this->path));

            return;
        }
        list($path, $threadId, $threadItemId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread item JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectThreadItem $threadItem */
        $threadItem = $this->_jsonMapper->map($json, new DirectThreadItem());
        $this->_target->emit('direct-story-updated', [$threadId, $threadItemId, $threadItem]);
    }

    /**
     * Handler for ADD op.
     */
    protected function _handleAdd()
    {
        $handled = false;
        if ($this->_isPathStartsWith('/direct_v2/threads')) {
            if (strpos($this->path, 'activity_indicator_id') === false) {
                $this->_upsertThreadItem();
                $handled = true;
            } else {
                $this->_upsertThreadActivity();
                $handled = true;
            }
        } elseif ($this->_isPathStartsWith('/direct_v2/inbox/threads')) {
            $this->_upsertThread();
            $handled = true;
        } elseif ($this->_isPathStartsWith('/broadcast')) {
            $this->_handleLiveBroadcast();
            $handled = true;
        } elseif ($this->_isPathStartsWith('/direct_v2/visual_threads')) {
            $this->_updateDirectStory();
            $handled = true;
        }

        if (!$handled) {
            $this->_logger->warning(sprintf('Unsupported ADD path "%s"', $this->path));
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

        $payload = new DirectSeenItemPayload();
        $payload->count = (int) $this->value;
        $payload->timestamp = $this->ts;
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

        if (!preg_match('#^/direct_v2/threads/([^/]+)/participants/([^/]+)/has_seen$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread seen regexp', $this->path));

            return;
        }
        list($path, $threadId, $userId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread seen JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectThreadLastSeenAt $lastSeenAt */
        $lastSeenAt = $this->_jsonMapper->map($json, new DirectThreadLastSeenAt());
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

        if (!preg_match('#^/direct_v2/visual_thread/([^/]+)/screenshot$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread screenshot regexp', $this->path));

            return;
        }
        list($path, $threadId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var EventPayload\Screenshot $screenshot */
        $screenshot = $this->_jsonMapper->map($json, new EventPayload\Screenshot());
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

        if ($this->path !== '/direct_v2/visual_thread/create') {
            $this->_logger->warning(sprintf('Path %s does not match story create path', $this->path));

            return;
        }
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode inbox JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var DirectInbox $inbox */
        $inbox = $this->_jsonMapper->map($json, new DirectInbox());
        if (!isset($inbox->threads) || !count($inbox->threads)) {
            return;
        }
        /** @var DirectThread $thread */
        $thread = reset($inbox->threads);
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

        if (!preg_match('#^/direct_v2/visual_action_badge/(.+)$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match story action regexp', $this->path));

            return;
        }
        list($path, $threadId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode story action JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var ActionBadge $storyAction */
        $storyAction = $this->_jsonMapper->map($json, new ActionBadge());
        $this->_target->emit('direct-story-action', [$threadId, $storyAction]);
    }

    /**
     * Handler for REPLACE op.
     */
    protected function _handleReplace()
    {
        $handled = false;
        if ($this->_isPathStartsWith('/direct_v2/threads')) {
            if ($this->_isPathEndsWith('has_seen')) {
                $this->_updateSeen();
                $handled = true;
            } else {
                $this->_upsertThreadItem();
                $handled = true;
            }
        } elseif ($this->_isPathStartsWith('/direct_v2/inbox/threads')) {
            $this->_upsertThread();
            $handled = true;
        } elseif ($this->_isPathStartsWith('/direct_v2/inbox') || $this->_isPathStartsWith('/direct_v2/visual_inbox')) {
            if ($this->_isPathEndsWith('unseen_count')) {
                $this->_updateUnseenCount();
                $handled = true;
            }
        } elseif ($this->_isPathStartsWith('/direct_v2/visual_action_badge')) {
            $this->_directStoryAction();
            $handled = true;
        } elseif ($this->_isPathStartsWith('/direct_v2/visual_thread')) {
            if ($this->_isPathEndsWith('screenshot')) {
                $this->_notifyDirectStoryScreenshot();
                $handled = true;
            } elseif ($this->_isPathEndsWith('create')) {
                $this->_createDirectStory();
                $handled = true;
            }
        }

        if (!$handled) {
            $this->_logger->warning(sprintf('Unsupported REPLACE path "%s"', $this->path));
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

        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread item regexp', $this->path));

            return;
        }
        list($path, $threadId, $threadItemId) = $matches;
        $this->_target->emit('thread-item-removed', [$threadId, $threadItemId]);
    }

    /**
     * Handler for REMOVE op.
     */
    protected function _handleRemove()
    {
        $handled = false;
        if ($this->_isPathStartsWith('/direct_v2')) {
            $this->_removeThreadItem();
            $handled = true;
        } elseif ($this->_isPathStartsWith('/broadcast')) {
            $this->_handleLiveBroadcast();
            $handled = true;
        }

        if (!$handled) {
            $this->_logger->warning(sprintf('Unsupported REMOVE path "%s"', $this->path));
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

        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $this->path, $matches)) {
            $this->_logger->warning(sprintf('Path %s does not match thread item regexp', $this->path));

            return;
        }
        list($path, $threadId, $threadItemId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_logger->warning(sprintf('Failed to decode thread item notify JSON: %s', json_last_error_msg()));

            return;
        }
        /** @var EventPayload\Notify $notifyPayload */
        $notifyPayload = $this->_jsonMapper->map($json, new EventPayload\Notify());
        $this->_target->emit('thread-notify', [$threadId, $threadItemId, $notifyPayload]);
    }

    /**
     * Handler for NOTIFY op.
     */
    protected function _handleNotify()
    {
        $handled = false;
        if ($this->_isPathStartsWith('/direct_v2/threads')) {
            $this->_notifyThread();
            $handled = true;
        }

        if (!$handled) {
            $this->_logger->warning(sprintf('Unsupported NOTIFY path "%s"', $this->path));
        }
    }

    /**
     * @param EventEmitterInterface $target
     * @param JsonMapper            $jsonMapper
     * @param LoggerInterface       $logger
     */
    public function handle(
        EventEmitterInterface $target,
        JsonMapper $jsonMapper,
        LoggerInterface $logger)
    {
        $this->_target = $target;
        $this->_jsonMapper = $jsonMapper;
        $this->_logger = $logger;
        switch ($this->op) {
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
                $this->_logger->warning(sprintf('Unknown patch op "%s"', $this->op));
        }
    }
}
