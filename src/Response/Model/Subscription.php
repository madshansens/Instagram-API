<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Subscription extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'topic'    => '',
        'url'      => '',
        'sequence' => '',
        'auth'     => '',
    ];
}
