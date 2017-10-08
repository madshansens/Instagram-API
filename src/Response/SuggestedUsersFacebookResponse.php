<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SuggestedUsersFacebookResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'suggested'  => 'Model\Suggested[]',
        'rank_token' => '',
    ];
}
