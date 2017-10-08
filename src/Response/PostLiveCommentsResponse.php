<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class PostLiveCommentsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'starting_offset'   => '',
        'ending_offset'     => '',
        'next_fetch_offset' => '',
        'comments'          => 'Model\LiveComment[]',
        'pinned_comments'   => 'Model\LiveComment[]',
    ];
}
