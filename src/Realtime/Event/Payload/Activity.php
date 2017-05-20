<?php

namespace InstagramAPI\Realtime\Event\Payload;

class Activity extends \InstagramAPI\AutoPropertyHandler
{
    public $timestamp;
    /** @var string */
    public $sender_id;
    public $activity_status;
    public $ttl;
}
