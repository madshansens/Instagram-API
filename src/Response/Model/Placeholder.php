<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Placeholder extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'is_linked' => '',
        'title'     => '',
        'message'   => '',
    ];
}
