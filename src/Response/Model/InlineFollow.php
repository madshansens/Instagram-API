<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class InlineFollow extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user_info'        => 'User',
        'following'        => '',
        'outgoing_request' => '',
    ];
}
