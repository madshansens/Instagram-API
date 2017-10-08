<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Suggestion extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media_infos'    => '',
        'social_context' => '',
        'algorithm'      => '',
        'thumbnail_urls' => 'string[]',
        'value'          => '',
        'caption'        => '',
        'user'           => 'User',
        'large_urls'     => 'string[]',
        'media_ids'      => '',
        'icon'           => '',
    ];
}
