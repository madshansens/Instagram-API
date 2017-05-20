<?php

namespace InstagramAPI\Realtime\Event\Patch;

use InstagramAPI\Client as HttpClient;
use InstagramAPI\Realtime;
use InstagramAPI\Realtime\Action\Payload as ActionPayload;
use InstagramAPI\Realtime\Client;
use InstagramAPI\Realtime\Event\Payload as EventPayload;
use InstagramAPI\Response\Model\Inbox;
use InstagramAPI\Response\Model\Thread;
use InstagramAPI\Response\Model\ThreadItem;
use InstagramAPI\Response\Model\ThreadLastSeenAt;

class Op extends \InstagramAPI\AutoPropertyHandler
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

    /** @var Realtime */
    protected $_rtc;

    /** @var Client */
    protected $_client;

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
     * Handler for thread item creation/modification.
     */
    protected function _upsertThreadItem()
    {
        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match thread item regexp', $this->path);

            return;
        }
        list($path, $threadId, $threadItemId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode thread item JSON: %s', json_last_error_msg());

            return;
        }
        /** @var ThreadItem $threadItem */
        $threadItem = $this->_client->mapToJson($json, new ThreadItem());
        switch ($this->op) {
            case self::ADD:
                $this->_rtc->emit('thread-item-created', [$threadId, $threadItemId, $threadItem]);
                break;
            case self::REPLACE:
                $this->_rtc->emit('thread-item-updated', [$threadId, $threadItemId, $threadItem]);
                break;
            default:
                $this->_client->debug('Unsupported thread item op: "%s"', $this->op);
        }
    }

    /**
     * Handler for thread creation/modification.
     */
    protected function _upsertThread()
    {
        if (!preg_match('#^/direct_v2/inbox/threads/(.+)$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match thread regexp', $this->path);

            return;
        }
        list($path, $threadId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode thread JSON: %s', json_last_error_msg());

            return;
        }
        /** @var Thread $thread */
        $thread = $this->_client->mapToJson($json, new Thread());
        switch ($this->op) {
            case self::ADD:
                $this->_rtc->emit('thread-created', [$threadId, $thread]);
                break;
            case self::REPLACE:
                $this->_rtc->emit('thread-updated', [$threadId, $thread]);
                break;
            default:
                $this->_client->debug('Unsupported thread op: "%s"', $this->op);
        }
    }

    /**
     * Handler for live broadcast creation/removal.
     */
    protected function _handleLiveBroadcast()
    {
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode live broadcast JSON: %s', json_last_error_msg());

            return;
        }
        /** @var EventPayload\Live $livePayload */
        $livePayload = $this->_client->mapToJson($json, new EventPayload\Live());
        switch ($this->op) {
            case self::ADD:
                $this->_rtc->emit('live-started', [$livePayload]);
                break;
            case self::REMOVE:
                $this->_rtc->emit('live-stopped', [$livePayload]);
                break;
            default:
                $this->_client->debug('Unsupported live broadcast op: "%s"', $this->op);
        }
    }

    /**
     * Handler for thread activity indicator.
     */
    protected function _upsertThreadActivity()
    {
        if (!preg_match('#^/direct_v2/threads/([^/]+)/activity_indicator_id/(.+)$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match thread activity regexp', $this->path);

            return;
        }
        list($path, $threadId, $indicatorId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode thread activity JSON: %s', json_last_error_msg());

            return;
        }
        /** @var EventPayload\Activity $activity */
        $activity = $this->_client->mapToJson($json, new EventPayload\Activity());
        $this->_rtc->emit('thread-activity', [$threadId, $activity]);
    }

    /**
     * Handler for story update.
     */
    protected function _updateDirectStory()
    {
        if (!preg_match('#^/direct_v2/visual_threads/([^/]+)/items/(.+)$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match story item regexp', $this->path);

            return;
        }
        list($path, $threadId, $threadItemId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode thread item JSON: %s', json_last_error_msg());

            return;
        }
        /** @var ThreadItem $threadItem */
        $threadItem = $this->_client->mapToJson($json, new ThreadItem());
        $this->_rtc->emit('direct-story-updated', [$threadId, $threadItemId, $threadItem]);
    }

    /**
     * Handler for ADD op.
     */
    protected function _handleAdd()
    {
        if ($this->_isPathStartsWith('/direct_v2/threads')) {
            if (strpos($this->path, 'activity_indicator_id') === false) {
                $this->_upsertThreadItem();
            } else {
                $this->_upsertThreadActivity();
            }
        } elseif ($this->_isPathStartsWith('/direct_v2/inbox/threads')) {
            $this->_upsertThread();
        } elseif ($this->_isPathStartsWith('/broadcast')) {
            $this->_handleLiveBroadcast();
        } elseif ($this->_isPathStartsWith('/direct_v2/visual_threads')) {
            $this->_updateDirectStory();
        } else {
            $this->_client->debug('Unsupported ADD path "%s"', $this->path);
        }
    }

    /**
     * Handler for unseen count.
     */
    protected function _updateUnseenCount()
    {
        $payload = new ActionPayload\Unseen();
        $payload->count = (int) $this->value;
        $payload->timestamp = $this->ts;
        $this->_rtc->emit('unseen-count-update', [$payload]);
    }

    /**
     * Handler for thread seen indicator.
     */
    protected function _updateSeen()
    {
        if (!preg_match('#^/direct_v2/threads/([^/]+)/participants/([^/]+)/has_seen$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match thread seen regexp', $this->path);

            return;
        }
        list($path, $threadId, $userId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode thread seen JSON: %s', json_last_error_msg());

            return;
        }
        /** @var ThreadLastSeenAt $lastSeenAt */
        $lastSeenAt = $this->_client->mapToJson($json, new ThreadLastSeenAt());
        $this->_rtc->emit('thread-seen', [$threadId, $userId, $lastSeenAt]);
    }

    /**
     * Handler for screenshot notification.
     */
    protected function _notifyDirectStoryScreenshot()
    {
        if (!preg_match('#^/direct_v2/visual_thread/([^/]+)/screenshot$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match thread screenshot regexp', $this->path);

            return;
        }
        list($path, $threadId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode thread JSON: %s', json_last_error_msg());

            return;
        }
        /** @var EventPayload\Screenshot $screenshot */
        $screenshot = $this->_client->mapToJson($json, new EventPayload\Screenshot());
        $this->_rtc->emit('direct-story-screenshot', [$threadId, $screenshot]);
    }

    /**
     * Handler for direct story creation.
     */
    protected function _createDirectStory()
    {
        if ($this->path !== '/direct_v2/visual_thread/create') {
            $this->_client->debug('Path %s does not match story create path', $this->path);

            return;
        }
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode inbox JSON: %s', json_last_error_msg());

            return;
        }
        /** @var Inbox $inbox */
        $inbox = $this->_client->mapToJson($json, new Inbox());
        if (!isset($inbox->threads) || !count($inbox->threads)) {
            return;
        }
        /** @var Thread $thread */
        $thread = reset($inbox->threads);
        $this->_rtc->emit('direct-story-created', [$thread]);
    }

    /**
     * Handler for story action.
     */
    protected function _directStoryAction()
    {
        if (!preg_match('#^/direct_v2/visual_action_badge/(.+)$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match story action regexp', $this->path);

            return;
        }
        list($path, $threadId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode story action JSON: %s', json_last_error_msg());

            return;
        }
        /** @var EventPayload\StoryAction $storyAction */
        $storyAction = $this->_client->mapToJson($json, new EventPayload\StoryAction());
        $this->_rtc->emit('direct-story-action', [$threadId, $storyAction]);
    }

    /**
     * Handler for REPLACE op.
     */
    protected function _handleReplace()
    {
        if ($this->_isPathStartsWith('/direct_v2/threads')) {
            if ($this->_isPathEndsWith('has_seen')) {
                $this->_updateSeen();
            } else {
                $this->_upsertThreadItem();
            }
        } elseif ($this->_isPathStartsWith('/direct_v2/inbox/threads')) {
            $this->_upsertThread();
        } elseif ($this->_isPathStartsWith('/direct_v2/inbox') || $this->_isPathStartsWith('/direct_v2/visual_inbox')) {
            if ($this->_isPathEndsWith('unseen_count')) {
                $this->_updateUnseenCount();
            }
        } elseif ($this->_isPathStartsWith('/direct_v2/visual_action_badge')) {
            $this->_directStoryAction();
        } elseif ($this->_isPathStartsWith('/direct_v2/visual_thread')) {
            if ($this->_isPathEndsWith('screenshot')) {
                $this->_notifyDirectStoryScreenshot();
            } elseif ($this->_isPathEndsWith('create')) {
                $this->_createDirectStory();
            }
        } else {
            $this->_client->debug('Unsupported REPLACE path "%s"', $this->path);
        }
    }

    /**
     * Handler for thread item removal.
     */
    protected function _removeThreadItem()
    {
        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match thread item regexp', $this->path);

            return;
        }
        list($path, $threadId, $threadItemId) = $matches;
        $this->_rtc->emit('thread-item-removed', [$threadId, $threadItemId]);
    }

    /**
     * Handler for REMOVE op.
     */
    protected function _handleRemove()
    {
        if ($this->_isPathStartsWith('/direct_v2')) {
            $this->_removeThreadItem();
        } elseif ($this->_isPathStartsWith('/broadcast')) {
            $this->_handleLiveBroadcast();
        } else {
            $this->_client->debug('Unsupported REMOVE path "%s"', $this->path);
        }
    }

    /**
     * Handler for thread notify.
     */
    protected function _notifyThread()
    {
        if (!preg_match('#^/direct_v2/threads/([^/]+)/items/(.+)$#D', $this->path, $matches)) {
            $this->_client->debug('Path %s does not match thread item regexp', $this->path);

            return;
        }
        list($path, $threadId, $threadItemId) = $matches;
        $json = HttpClient::api_body_decode($this->value);
        if (!is_object($json)) {
            $this->_client->debug('Failed to decode thread item notify JSON: %s', json_last_error_msg());

            return;
        }
        /** @var EventPayload\Notify $notifyPayload */
        $notifyPayload = $this->_client->mapToJson($json, new EventPayload\Notify());
        $this->_rtc->emit('thread-notify', [$threadId, $threadItemId, $notifyPayload]);
    }

    /**
     * Handler for NOTIFY op.
     */
    protected function _handleNotify()
    {
        if ($this->_isPathStartsWith('/direct_v2/threads')) {
            $this->_notifyThread();
        } else {
            $this->_client->debug('Unsupported NOTIFY path "%s"', $this->path);
        }
    }

    /**
     * @param Client $client
     */
    public function handle(
        Client $client)
    {
        $this->_client = $client;
        $this->_rtc = $client->getRtc();
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
                $this->_client->debug('Unknown patch op "%s"', $this->op);
        }
    }
}
