<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class BroadcastInfoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'id'                     => 'string',
        'broadcast_message'      => '',
        'organic_tracking_token' => '',
        'published_time'         => '',
        'broadcast_status'       => '',
        'media_id'               => 'string',
        'broadcast_owner'        => 'Model\User',
    ];
}
