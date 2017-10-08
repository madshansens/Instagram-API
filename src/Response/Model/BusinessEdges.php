<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class BusinessEdges extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'node'   => 'BusinessNode',
        'cursor' => '',
    ];
}
