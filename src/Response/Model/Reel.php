<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Reel extends AutoPropertyHandler
{
    // NOTE: We must use full paths to all model objects in THIS class, because
    // "UserReelMediaFeedResponse" re-uses this object and JSONMapper won't be
    // able to find these sub-objects if the paths aren't absolute!

    /**
     * @var string
     */
    public $id;
    /**
     * @var \InstagramAPI\Response\Model\Item[]
     */
    public $items;
    /**
     * @var \InstagramAPI\Response\Model\User
     */
    public $user;
    public $expiring_at;
    public $seen;
    public $can_reply;
    /**
     * @var \InstagramAPI\Response\Model\Location
     */
    public $location;
    public $latest_reel_media;
    public $prefetch_count;
    /**
     * @var \InstagramAPI\Response\Model\Broadcast
     */
    public $broadcast;
}
