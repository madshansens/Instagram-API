<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class BusinessPageInfo extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'has_next_page'     => '',
        'has_previous_page' => '',
    ];
}
