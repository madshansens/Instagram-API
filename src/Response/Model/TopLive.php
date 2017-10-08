<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class TopLive extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'broadcast_owners' => 'BroadcastOwner[]',
        'ranked_position'  => '',
    ];
}
