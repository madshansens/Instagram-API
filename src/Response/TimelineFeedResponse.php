<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class TimelineFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'num_results'            => '',
        'is_direct_v2_enabled'   => '',
        'auto_load_more_enabled' => '',
        'more_available'         => '',
        'next_max_id'            => 'string',
        'feed_items'             => 'Model\FeedItem[]',
        'megaphone'              => 'Model\FeedAysf',
    ];
}
