<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class BroadcastOwner extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'pk'                => 'string',
        'friendship_status' => 'FriendshipStatus',
        'full_name'         => '',
        'is_verified'       => '',
        'profile_pic_url'   => '',
        'profile_pic_id'    => 'string',
        'is_private'        => '',
        'username'          => '',
    ];
}
