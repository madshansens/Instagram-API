<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Aymf extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'items'          => 'Item[]',
        'more_available' => '',
    ];
}
