<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class UserStoryFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'broadcast'      => 'Model\Broadcast',
        'reel'           => 'Model\Reel',
        'post_live_item' => 'Model\PostLiveItem',
    ];
}
