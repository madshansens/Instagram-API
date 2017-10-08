<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class BlockedListResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'blocked_list' => 'Model\User[]',
        'page_size'    => '',
    ];
}
