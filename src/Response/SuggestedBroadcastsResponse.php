<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SuggestedBroadcastsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'broadcasts' => 'Model\Broadcast[]',
    ];
}
