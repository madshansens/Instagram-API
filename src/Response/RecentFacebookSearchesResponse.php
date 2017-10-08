<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class RecentFacebookSearchesResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'recent' => 'Model\Suggested[]',
    ];
}
