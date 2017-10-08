<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Broadcast extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'broadcast_owner'           => 'BroadcastOwner',
        'broadcast_status'          => '',
        'cover_frame_url'           => '',
        'published_time'            => '',
        'broadcast_message'         => '',
        'muted'                     => '',
        'media_id'                  => 'string',
        'id'                        => 'string',
        'rtmp_playback_url'         => '',
        'dash_abr_playback_url'     => '',
        'dash_playback_url'         => '',
        'ranked_position'           => '',
        'organic_tracking_token'    => '',
        'seen_ranked_position'      => '',
        'viewer_count'              => '',
        'dash_manifest'             => '',
        'expire_at'                 => '',
        'encoding_tag'              => '',
        'total_unique_viewer_count' => '',
        'internal_only'             => '',
        'number_of_qualities'       => '',
    ];
}
