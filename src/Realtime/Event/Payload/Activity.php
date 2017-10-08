<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyMapper;

class Activity extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'timestamp'       => '',
        'sender_id'       => 'string',
        'activity_status' => '',
        'ttl'             => '',
    ];
}
