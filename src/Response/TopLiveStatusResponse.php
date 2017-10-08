<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class TopLiveStatusResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'broadcast_status_items' => 'Model\BroadcastStatusItem[]',
    ];
}
