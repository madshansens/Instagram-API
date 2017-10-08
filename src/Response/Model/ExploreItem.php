<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class ExploreItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media'             => 'Item',
        'stories'           => 'Stories',
        'channel'           => 'Channel',
        'explore_item_info' => 'ExploreItemInfo',
    ];
}
