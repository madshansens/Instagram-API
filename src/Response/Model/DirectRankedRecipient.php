<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectRankedRecipient extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'thread' => 'DirectThread',
        'user'   => 'User',
    ];
}
