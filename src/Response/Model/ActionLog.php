<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class ActionLog extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'bold'        => 'Bold[]',
        'description' => '',
    ];
}
