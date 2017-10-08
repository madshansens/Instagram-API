<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SearchUserResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'has_more'    => '',
        'num_results' => '',
        'next_max_id' => 'string',
        'users'       => 'Model\User[]',
        'rank_token'  => '',
    ];
}
