<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectMessageMetadata extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'thread_id'       => 'string',
        'item_id'         => 'string',
        'timestamp'       => 'string',
        'participant_ids' => 'string[]',
    ];
}
