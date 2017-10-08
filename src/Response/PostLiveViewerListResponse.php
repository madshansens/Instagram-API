<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class PostLiveViewerListResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'users'              => 'Model\User[]',
        'next_max_id'        => '',
        'total_viewer_count' => '',
    ];
}
