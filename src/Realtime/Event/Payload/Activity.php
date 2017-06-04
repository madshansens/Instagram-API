<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyHandler;

class Activity extends AutoPropertyHandler
{
    public $timestamp;
    /** @var string */
    public $sender_id;
    public $activity_status;
    public $ttl;
}
