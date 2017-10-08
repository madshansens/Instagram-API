<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Media extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'image'       => '',
        'id'          => 'string',
        'user'        => 'User',
        'expiring_at' => '',
    ];
}
