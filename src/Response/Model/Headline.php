<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Headline extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'content_type'   => '',
        'user'           => 'User',
        'user_id'        => 'string',
        'pk'             => 'string',
        'text'           => '',
        'type'           => '',
        'created_at'     => 'string',
        'created_at_utc' => 'string',
        'media_id'       => 'string',
        'bit_flags'      => '',
        'status'         => '',
    ];
}
