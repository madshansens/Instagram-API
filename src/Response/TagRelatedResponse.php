<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class TagRelatedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'related' => 'Model\Related[]',
    ];
}
