<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ExploreResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'num_results'            => '',
        'auto_load_more_enabled' => '',
        'items'                  => 'Model\ExploreItem[]',
        'more_available'         => '',
        'next_max_id'            => 'string',
        'max_id'                 => 'string',
        'rank_token'             => '',
    ];
}
