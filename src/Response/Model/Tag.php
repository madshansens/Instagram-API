<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Tag extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media_count' => '',
        'name'        => '',
        'id'          => 'string',
    ];
}
