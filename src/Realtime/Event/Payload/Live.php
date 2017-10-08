<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyMapper;

class Live extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user'                 => '\InstagramAPI\Response\Model\User',
        'broadcast_id'         => 'string',
        'is_periodic'          => '',
        'broadcast_message'    => '',
        'display_notification' => '',
    ];
}
