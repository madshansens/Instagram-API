<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class MediaLikersResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'user_count' => '',
        'users'      => 'Model\User[]',
    ];
}
