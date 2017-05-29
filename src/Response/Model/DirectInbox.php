<?php

namespace InstagramAPI\Response\Model;

class DirectInbox extends \InstagramAPI\Response
{
    public $unseen_count;
    public $has_older;
    public $oldest_cursor;
    public $unseen_count_ts; // is a timestamp
    /**
     * @var DirectThread[]
     */
    public $threads;
}
