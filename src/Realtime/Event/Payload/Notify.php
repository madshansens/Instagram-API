<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyHandler;

class Notify extends AutoPropertyHandler
{
    /** @var string */
    public $user_id;
    /** @var \InstagramAPI\Response\Model\ActionLog */
    public $action_log;
}
