<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class LikeFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'auto_load_more_enabled' => '',
        'items'                  => 'Model\Item[]',
        'more_available'         => '',
        'patches'                => '',
        'last_counted_at'        => '',
        'num_results'            => '',
        'next_max_id'            => 'string',
    ];
}
