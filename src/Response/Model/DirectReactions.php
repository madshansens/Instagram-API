<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectReactions extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'likes_count' => 'int',
        'likes'       => 'DirectReaction[]',
    ];
}
