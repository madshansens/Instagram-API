<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Reel extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                => 'string',
        'items'             => 'Item[]',
        'user'              => 'User',
        'expiring_at'       => '',
        'seen'              => '',
        'can_reply'         => '',
        'location'          => 'Location',
        'latest_reel_media' => '',
        'prefetch_count'    => '',
        'broadcast'         => 'Broadcast',
    ];
}
