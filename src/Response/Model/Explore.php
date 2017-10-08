<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Explore extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'explanation'  => '',
        'actor_id'     => 'string',
        'source_token' => '',
    ];
}
