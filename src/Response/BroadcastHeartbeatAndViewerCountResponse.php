<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class BroadcastHeartbeatAndViewerCountResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'broadcast_status' => '',
        'viewer_count'     => '',
    ];
}
