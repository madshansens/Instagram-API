<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class BroadcastCommentsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'comments'                   => 'Model\Comment[]',
        'comment_count'              => '',
        'live_seconds_per_comment'   => '',
        'has_more_headload_comments' => '',
        'is_first_fetch'             => '',
        'comment_likes_enabled'      => '',
        'pinned_comment'             => 'Model\Comment',
        'system_comments'            => '',
        'has_more_comments'          => '',
        'caption_is_edited'          => '',
        'caption'                    => '',
        'comment_muted'              => '',
    ];
}
