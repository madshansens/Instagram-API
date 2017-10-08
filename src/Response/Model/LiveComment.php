<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class LiveComment extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'comment' => 'Comment',
        'offset'  => '',
        'event'   => '',
    ];
}
