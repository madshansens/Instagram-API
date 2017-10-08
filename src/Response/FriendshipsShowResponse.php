<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FriendshipsShowResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        Model\FriendshipStatus::class, // Import property map.
    ];
}
