<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class FriendshipStatus extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'following'        => '',
        'followed_by'      => '',
        'incoming_request' => '',
        'outgoing_request' => '',
        'is_private'       => '',
        'is_blocking_reel' => '',
        'is_muting_reel'   => '',
        'blocking'         => '',
        'is_bestie'        => '',
    ];
}
