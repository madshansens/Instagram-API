<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class DirectThread extends AutoPropertyHandler
{
    public $named;
    /**
     * @var User[]
     */
    public $users;
    public $has_newer;
    /**
     * @var string
     */
    public $viewer_id;
    /**
     * @var string
     */
    public $thread_id;
    public $last_activity_at;
    public $newest_cursor;
    public $is_spam;
    public $has_older;
    public $oldest_cursor;
    /**
     * @var User[]
     */
    public $left_users;
    public $muted;
    /**
     * @var DirectThreadItem[]
     */
    public $items;
    public $thread_type;
    public $thread_title;
    public $canonical;
    /**
     * @var User
     */
    public $inviter;
    public $pending;
    /**
     * @var DirectThreadLastSeenAt[]
     */
    public $last_seen_at;
    public $unseen_count;
    /**
     * @var ActionBadge
     */
    public $action_badge;
    public $last_activity_at_secs;
}
