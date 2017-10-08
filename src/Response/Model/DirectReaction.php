<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectReaction extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'reaction_type'   => 'string',
        'timestamp'       => 'string',
        'sender_id'       => 'string',
        'client_context'  => 'string',
        'reaction_status' => 'string',
        'node_type'       => 'string',
        'item_id'         => 'string',
    ];
}
