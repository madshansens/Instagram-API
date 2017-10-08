<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class ExploreItemInfo extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'num_columns'       => '',
        'total_num_columns' => '',
        'aspect_ratio'      => '',
        'autoplay'          => '',
    ];
}
