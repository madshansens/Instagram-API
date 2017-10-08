<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class PopularFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'next_max_id'            => 'string',
        'more_available'         => '',
        'auto_load_more_enabled' => '',
        'items'                  => 'Model\Item[]',
        'num_results'            => '',
        'max_id'                 => 'string',
    ];
}
