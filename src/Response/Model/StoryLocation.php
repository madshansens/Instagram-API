<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class StoryLocation extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'rotation'    => 'float',
        'x'           => 'float',
        'y'           => 'float',
        'height'      => 'float',
        'width'       => 'float',
        'location'    => 'Location',
        'attribution' => 'Attribution',
        'is_pinned'   => '',
    ];
}
