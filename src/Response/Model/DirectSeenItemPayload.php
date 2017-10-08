<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectSeenItemPayload extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'count'     => '',
        'timestamp' => 'string',
    ];
}
