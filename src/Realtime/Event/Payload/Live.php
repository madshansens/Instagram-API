<?php

namespace InstagramAPI\Realtime\Event\Payload;

class Live extends \InstagramAPI\AutoPropertyHandler
{
    /** @var \InstagramAPI\Response\Model\User */
    public $user;
    /** @var string */
    public $broadcast_id;
    public $is_periodic;
    public $broadcast_message;
    public $display_notification;
}
