<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SavedFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'items'                  => 'Model\SavedFeedItem[]',
        'more_available'         => '',
        'next_max_id'            => 'string',
        'auto_load_more_enabled' => '',
        'num_results'            => '',
    ];
}
