<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectThread extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'named'                 => '',
        'users'                 => 'User[]',
        'has_newer'             => '',
        'viewer_id'             => 'string',
        'thread_id'             => 'string',
        'last_activity_at'      => '',
        'newest_cursor'         => '',
        'is_spam'               => '',
        'has_older'             => '',
        'oldest_cursor'         => '',
        'left_users'            => 'User[]',
        'muted'                 => '',
        'items'                 => 'DirectThreadItem[]',
        'thread_type'           => '',
        'thread_title'          => '',
        'canonical'             => '',
        'inviter'               => 'User',
        'pending'               => '',
        'last_seen_at'          => 'DirectThreadLastSeenAt[]',
        'unseen_count'          => '',
        'action_badge'          => 'ActionBadge',
        'last_activity_at_secs' => '',
        'last_permanent_item'   => 'PermanentItem',
        'is_pin'                => '',
    ];
}
