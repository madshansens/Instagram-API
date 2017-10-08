<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DiscoverTopLiveResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'auto_load_more_enabled' => '',
        'broadcasts'             => 'Model\BroadcastItem[]',
        'more_available'         => '',
        'next_max_id'            => 'string',
    ];
}
