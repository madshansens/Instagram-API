<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class MediaInsightsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'media_organic_insights' => 'Model\MediaInsights[]',
    ];
}
