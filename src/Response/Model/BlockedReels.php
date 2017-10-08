<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class BlockedReels extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'users'     => 'User[]',
        'page_size' => '',
        'big_list'  => '',
    ];
}
