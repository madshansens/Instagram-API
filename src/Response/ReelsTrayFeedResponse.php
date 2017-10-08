<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ReelsTrayFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'tray'                    => 'Model\StoryTray[]',
        'broadcasts'              => 'Model\Broadcast[]',
        'post_live'               => 'Model\PostLive',
        'sticker_version'         => '',
        'face_filter_nux_version' => '',
        'story_ranking_token'     => '',
    ];
}
