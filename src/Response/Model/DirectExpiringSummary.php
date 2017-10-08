<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectExpiringSummary extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'type'      => 'string',
        'timestamp' => 'string',
        'count'     => 'int',
    ];
}
