<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyMapper;

class Notify extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user_id'    => 'string',
        'action_log' => '\InstagramAPI\Response\Model\ActionLog',
    ];
}
