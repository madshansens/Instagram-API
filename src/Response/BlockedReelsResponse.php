<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class BlockedReelsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        Model\BlockedReels::class, // Import property map.
        'next_max_id' => 'string',
    ];
}
