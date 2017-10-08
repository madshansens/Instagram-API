<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FinalViewerListResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'users'                     => 'Model\User[]',
        'total_unique_viewer_count' => '',
    ];
}
