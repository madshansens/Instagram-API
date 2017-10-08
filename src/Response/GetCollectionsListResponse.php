<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class GetCollectionsListResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'items'                  => 'Model\Item[]',
        'more_available'         => '',
        'auto_load_more_enabled' => '',
    ];
}
