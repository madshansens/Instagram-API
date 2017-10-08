<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DiscoverChannelsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'auto_load_more_enabled' => '',
        'items'                  => 'Model\Item[]',
        'more_available'         => '',
        'next_max_id'            => 'string',
    ];
}
