<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class UserInfoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'megaphone' => '',
        'user'      => 'Model\User',
    ];
}
