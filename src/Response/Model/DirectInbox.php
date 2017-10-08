<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectInbox extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'unseen_count'    => '',
        'has_older'       => '',
        'oldest_cursor'   => '',
        'unseen_count_ts' => '', // Is a timestamp.
        'threads'         => 'DirectThread[]',
    ];
}
