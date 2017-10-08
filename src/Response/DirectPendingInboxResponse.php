<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectPendingInboxResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'seq_id'                 => 'string',
        'pending_requests_total' => '',
        'inbox'                  => 'Model\DirectInbox',
    ];
}
