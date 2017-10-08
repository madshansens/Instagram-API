<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Link extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'start'        => '',
        'end'          => '',
        'id'           => 'string',
        'type'         => '',
        'text'         => '',
        'link_context' => 'LinkContext',
    ];
}
