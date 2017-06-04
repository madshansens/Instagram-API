<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class DirectVisualInboxResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $unseen_count;
    public $has_more_unread;
    public $read_cursor;
    public $has_more_read;
    public $unread_cursor;
    /**
     * @var Model\DirectThread[]
     */
    public $threads;
}
