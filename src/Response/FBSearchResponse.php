<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FBSearchResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'has_more'   => '',
        'hashtags'   => '',
        'users'      => '',
        'places'     => '',
        'rank_token' => '',
    ];
}
