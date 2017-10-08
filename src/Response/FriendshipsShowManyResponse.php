<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FriendshipsShowManyResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'friendship_statuses' => 'Model\FriendshipStatus[]',
    ];
}
