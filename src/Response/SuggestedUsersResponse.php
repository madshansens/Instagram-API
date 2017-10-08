<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SuggestedUsersResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'users'     => 'Model\User[]',
        'is_backup' => '',
    ];
}
