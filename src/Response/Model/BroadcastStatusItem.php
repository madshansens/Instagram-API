<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class BroadcastStatusItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'broadcast_status'       => '',
        'has_reduced_visibility' => '',
        'cover_frame_url'        => '',
        'viewer_count'           => '',
        'id'                     => 'string',
    ];
}
