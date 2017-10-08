<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class GraphQuery extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'response' => 'QueryResponse',
        'error'    => '',
    ];
}
