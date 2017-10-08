<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Story extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'pk'         => 'string',
        'counts'     => 'Counts',
        'args'       => 'Args',
        'type'       => '',
        'story_type' => '',
    ];
}
