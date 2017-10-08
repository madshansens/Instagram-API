<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyMapper;

class Screenshot extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'action_user_dict' => '\InstagramAPI\Response\Model\User',
        'media_type'       => '',
    ];
}
