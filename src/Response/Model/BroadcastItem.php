<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class BroadcastItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'organic_tracking_token' => '',
        'published_time'         => '',
        'id'                     => 'string',
        'rtmp_playback_url'      => '',
        'cover_frame_url'        => '',
        'broadcast_status'       => '',
        'media_id'               => 'string',
        'broadcast_message'      => '',
        'viewer_count'           => '',
        'dash_abr_playback_url'  => '',
        'dash_playback_url'      => '',
        'broadcast_owner'        => 'User',
    ];
}
