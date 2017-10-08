<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class MediaInsights extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'reach_count'      => 'string[]',
        'impression_count' => '',
        'engagement_count' => '',
    ];
}
