<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyHandler;

class Screenshot extends AutoPropertyHandler
{
    /** @var \InstagramAPI\Response\Model\User */
    public $action_user_dict;
    public $media_type;
}
