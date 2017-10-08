<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class FeedItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media_or_ad'     => 'Item',
        'ad4ad'           => 'Ad4ad',
        'suggested_users' => 'SuggestedUsers',
        'ad_link_type'    => '',
    ];
}
