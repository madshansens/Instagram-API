<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectShareInboxResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'shares'          => '',
        'max_id'          => 'string',
        'new_shares'      => '',
        'patches'         => '',
        'last_counted_at' => '',
        'new_shares_info' => '',
    ];
}
