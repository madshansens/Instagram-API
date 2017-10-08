<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ReelMediaViewerResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'users'              => 'Model\User[]',
        'next_max_id'        => 'string',
        'user_count'         => '',
        'total_viewer_count' => '',
    ];
}
