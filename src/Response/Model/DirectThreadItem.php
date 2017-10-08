<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectThreadItem extends AutoPropertyMapper
{
    const PLACEHOLDER = 'placeholder';
    const TEXT = 'text';
    const HASHTAG = 'hashtag';
    const LOCATION = 'location';
    const PROFILE = 'profile';
    const MEDIA = 'media';
    const MEDIA_SHARE = 'media_share';
    const EXPIRING_MEDIA = 'raven_media';
    const LIKE = 'like';
    const ACTION_LOG = 'action_log';
    const REACTION = 'reaction';
    const REEL_SHARE = 'reel_share';
    const LINK = 'link';

    const JSON_PROPERTY_MAP = [
        'item_id'                       => 'string',
        'item_type'                     => '',
        'text'                          => '',
        'media_share'                   => 'Item',
        'media'                         => 'DirectThreadItemMedia',
        'user_id'                       => 'string',
        'timestamp'                     => '',
        'client_context'                => '',
        'hide_in_thread'                => '',
        'action_log'                    => 'ActionLog',
        'link'                          => 'DirectLink',
        'reactions'                     => 'DirectReactions',
        'raven_media'                   => 'Item',
        'seen_user_ids'                 => 'string[]',
        'expiring_media_action_summary' => 'DirectExpiringSummary',
        'reel_share'                    => 'ReelShare',
        'placeholder'                   => 'Placeholder',
        'location'                      => 'Location',
        'like'                          => '',
        'live_video_share'              => '',
    ];
}
