<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Groups extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'type'  => '',
        'items' => 'Item[]',
    ];
}
