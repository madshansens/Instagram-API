<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class MediaCommentsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'comments'                   => 'Model\Comment[]',
        'comment_count'              => '',
        'comment_likes_enabled'      => '',
        'next_max_id'                => 'string',
        'caption'                    => 'Model\Caption',
        'has_more_comments'          => '',
        'caption_is_edited'          => '',
        'preview_comments'           => '',
        'has_more_headload_comments' => '',
    ];
}
