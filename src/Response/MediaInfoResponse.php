<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class MediaInfoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'auto_load_more_enabled' => '',
        'num_results'            => '',
        'more_available'         => '',
        'items'                  => 'Model\Item[]',
    ];
}
