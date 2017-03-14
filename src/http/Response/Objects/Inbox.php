<?php

namespace InstagramAPI;

class Inbox extends Response
{
    public $unseen_count;
    public $has_older;
    public $oldest_cursor;
    public $unseen_count_ts; // is a timestamp
    /**
     * @var Thread[]
     */
    public $threads;
}
