<?php

namespace InstagramAPI\Response;

class VisualInboxResponse extends \InstagramAPI\Response
{
    public $unseen_count;
    public $has_more_unread;
    public $read_cursor;
    public $has_more_read;
    public $unread_cursor;
    /**
     * @var Model\Thread[]
     */
    public $threads;
}
