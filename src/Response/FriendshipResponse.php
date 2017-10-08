<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FriendshipResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'friendship_status' => 'Model\FriendshipStatus',
    ];
}
