<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class PostLive extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'post_live_items' => 'PostLiveItem[]',
    ];
}
