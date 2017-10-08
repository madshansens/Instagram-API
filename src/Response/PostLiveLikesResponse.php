<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class PostLiveLikesResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'starting_offset'   => '',
        'ending_offset'     => '',
        'next_fetch_offset' => '',
        'time_series'       => '',
    ];
}
