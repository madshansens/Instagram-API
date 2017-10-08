<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Users extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'position' => '',
        'user'     => 'User',
    ];
}
