<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Comment extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'status'             => '',
        'user_id'            => 'string',
        'created_at_utc'     => '',
        'created_at'         => '',
        'bit_flags'          => '',
        'user'               => 'User',
        'pk'                 => 'string',
        'media_id'           => 'string',
        'text'               => '',
        'content_type'       => '',
        'type'               => '',
        'comment_like_count' => '',
        'has_liked_comment'  => '',
        'has_translation'    => '',
        'did_report_as_spam' => '',
    ];
}
