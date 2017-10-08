<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectThreadLastSeenAt extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'item_id'   => 'string',
        'timestamp' => '',
    ];
}
