<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DirectSendItemPayload extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'client_request_id' => 'string',
        'client_context'    => 'string',
        'message'           => 'string',
        'item_id'           => 'string',
        'timestamp'         => 'string',
        'thread_id'         => 'string',
    ];
}
