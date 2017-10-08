<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class InsightsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'instagram_user' => 'Model\Insights[]',
    ];
}
