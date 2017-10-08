<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class PostLiveItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'pk'                     => 'string',
        'user'                   => 'User',
        'broadcasts'             => 'Broadcast[]',
        'last_seen_broadcast_ts' => '',
        'can_reply'              => '',
        'ranked_position'        => '',
        'seen_ranked_position'   => '',
        'muted'                  => '',
        'can_reshare'            => '',
    ];
}
