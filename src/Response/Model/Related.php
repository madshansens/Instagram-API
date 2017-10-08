<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Related extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'name' => '',
        'id'   => 'string',
        'type' => '',
    ];
}
