<?php

namespace InstagramAPI\Realtime\Event\Payload;

class Notify extends \InstagramAPI\AutoPropertyHandler
{
    /** @var string */
    public $user_id;
    /** @var \InstagramAPI\Response\Model\ActionLog */
    public $action_log;
}
