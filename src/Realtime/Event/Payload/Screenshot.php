<?php

namespace InstagramAPI\Realtime\Event\Payload;

class Screenshot extends \InstagramAPI\AutoPropertyHandler
{
    /** @var \InstagramAPI\Response\Model\User */
    public $action_user_dict;
    public $media_type;
}
