<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class SummaryPromotions extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'edges'     => 'BusinessEdges[]',
        'page_info' => 'BusinessPageInfo',
    ];
}
