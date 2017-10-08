<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectInboxResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'pending_requests_total' => '',
        'seq_id'                 => 'string',
        'pending_requests_users' => 'Model\User[]',
        'inbox'                  => 'Model\DirectInbox',
        'megaphone'              => 'Model\Megaphone',
    ];
}
