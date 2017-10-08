<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class BroadcastLikeCountResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'like_ts' => '',
        'likes'   => '',
        'likers'  => 'Model\User[]',
    ];
}
