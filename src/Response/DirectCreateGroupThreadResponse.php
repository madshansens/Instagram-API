<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectCreateGroupThreadResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'thread_id'        => 'string',
        'users'            => 'Model\User[]',
        'left_users'       => 'Model\User[]',
        'items'            => 'Model\DirectThreadItem[]',
        'last_activity_at' => '',
        'muted'            => '',
        'named'            => '',
        'canonical'        => '',
        'pending'          => '',
        'thread_type'      => '',
        'viewer_id'        => 'string',
        'thread_title'     => '',
        'inviter'          => 'Model\User',
        'has_older'        => '',
        'has_newer'        => '',
        'last_seen_at'     => '',
        'is_pin'           => '',
    ];
}
