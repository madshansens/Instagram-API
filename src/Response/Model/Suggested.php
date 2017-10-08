<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Suggested extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'position'    => '',
        'user'        => 'User',
        'client_time' => '',
    ];
}
