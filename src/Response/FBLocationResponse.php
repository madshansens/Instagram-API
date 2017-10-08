<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FBLocationResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'has_more'   => '',
        'items'      => 'Model\LocationItem[]',
        'rank_token' => '',
    ];
}
