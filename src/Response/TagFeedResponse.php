<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class TagFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'num_results'            => '',
        'ranked_items'           => 'Model\Item[]',
        'auto_load_more_enabled' => '',
        'items'                  => 'Model\Item[]',
        'story'                  => 'Model\StoryTray',
        'more_available'         => '',
        'next_max_id'            => 'string',
    ];
}
