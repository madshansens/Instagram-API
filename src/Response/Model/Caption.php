<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Caption extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'status'             => '',
        'user_id'            => 'string',
        'created_at_utc'     => '',
        'created_at'         => '',
        'bit_flags'          => '',
        'user'               => 'User',
        'content_type'       => '',
        'text'               => '',
        'media_id'           => 'string',
        'pk'                 => 'string',
        'type'               => '',
        'has_translation'    => '',
        'did_report_as_spam' => '',
    ];
}
