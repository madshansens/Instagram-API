<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Channel extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'channel_id'   => 'string',
        'channel_type' => '',
        'title'        => '',
        'header'       => '',
        'media_count'  => '',
        'media'        => 'Item',
        'context'      => '',
    ];
}
