<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Args extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media'                     => 'Media[]',
        'links'                     => 'Link[]',
        'text'                      => '',
        'profile_id'                => 'string',
        'profile_image'             => '',
        'timestamp'                 => '',
        'comment_id'                => 'string',
        'request_count'             => '',
        'action_url'                => '',
        'destination'               => '',
        'inline_follow'             => 'InlineFollow',
        'comment_ids'               => 'string[]',
        'second_profile_id'         => 'string',
        'second_profile_image'      => '',
        'profile_image_destination' => '',
        'tuuid'                     => '',
        'clicked'                   => '',
    ];
}
