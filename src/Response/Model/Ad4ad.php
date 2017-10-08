<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Ad4ad extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'type'           => '',
        'title'          => '',
        'media'          => 'Item',
        'footer'         => '',
        'id'             => 'string',
        'tracking_token' => '',
    ];
}
