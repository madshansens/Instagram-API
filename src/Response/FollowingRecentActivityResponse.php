<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FollowingRecentActivityResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'stories'                => 'Model\Story[]',
        'next_max_id'            => 'string',
        'auto_load_more_enabled' => '',
        'megaphone'              => '',
    ];
}
