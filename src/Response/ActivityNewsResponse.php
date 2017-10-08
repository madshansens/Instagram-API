<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ActivityNewsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'new_stories'            => 'Model\Story[]',
        'old_stories'            => 'Model\Story[]',
        'continuation'           => '',
        'friend_request_stories' => 'Model\Story[]',
        'counts'                 => 'Model\Counts',
        'subscription'           => 'Model\Subscription',
        'continuation_token'     => '',
        'ads_manager'            => '',
        'aymf'                   => 'Model\Aymf',
    ];
}
