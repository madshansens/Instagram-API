<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class StoryTray extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                   => 'string',
        'items'                => 'Item[]',
        'user'                 => 'User',
        'can_reply'            => '',
        'expiring_at'          => '',
        'seen_ranked_position' => '',
        'seen'                 => '',
        'latest_reel_media'    => '',
        'ranked_position'      => '',
        'is_nux'               => '',
        'show_nux_tooltip'     => '',
        'muted'                => '',
        'prefetch_count'       => '',
        'location'             => 'Location',
        'source_token'         => '',
        'owner'                => 'Owner',
        'nux_id'               => 'string',
        'dismiss_card'         => 'DismissCard',
        'can_reshare'          => '',
        'has_besties_media'    => '',
    ];
}
