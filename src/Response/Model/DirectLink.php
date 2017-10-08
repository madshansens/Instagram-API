<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectLink extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'text'         => 'string',
        'link_context' => 'LinkContext',
    ];
}
