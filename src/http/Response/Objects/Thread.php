<?php

namespace InstagramAPI;

class Thread extends Response
{
    public $named;
    /**
     * @var User[]
     */
    public $users;
    public $has_newer;
    public $viewer_id;
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
     * @var ThreadItem[]
     */
    public $items;
    public $thread_type;
    public $thread_title;
    public $canonical;
    /**
     * var User.
     */
    public $inviter;
    public $pending;
}
