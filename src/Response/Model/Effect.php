<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Effect extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'title'          => '',
        'id'             => 'string',
        'effect_id'      => 'string',
        'effect_file_id' => 'string',
        'asset_url'      => '',
        'thumbnail_url'  => '',
        'instructions'   => '',
    ];
}
