<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class LocationFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'media_count'            => '',
        'num_results'            => '',
        'auto_load_more_enabled' => '',
        'items'                  => 'Model\Item[]',
        'ranked_items'           => 'Model\Item[]',
        'more_available'         => '',
        'story'                  => 'Model\StoryTray',
        'location'               => 'Model\Location',
        'next_max_id'            => 'string',
    ];
}
