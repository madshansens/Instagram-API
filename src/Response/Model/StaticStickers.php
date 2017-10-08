<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class StaticStickers extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'include_in_recent' => '',
        'id'                => 'string',
        'stickers'          => 'Stickers[]',
    ];
}
