<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class AutoCompleteUserListResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'expires' => '',
        'users'   => 'Model\User[]',
    ];
}
