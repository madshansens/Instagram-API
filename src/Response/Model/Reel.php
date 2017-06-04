<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Reel extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $id;
    /**
     * @var Item[]
     */
    public $items;
    /**
     * @var User
     */
    public $user;
    public $expiring_at;
    public $seen;
    public $can_reply;
    /**
     * @var Location
     */
    public $location;
    public $latest_reel_media;
    public $prefetch_count;
    /**
     * @var Broadcast
     */
    public $broadcast;
}
