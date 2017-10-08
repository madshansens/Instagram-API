<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DiscoverPeopleResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'groups'         => 'Model\Groups[]',
        'more_available' => '',
        'max_id'         => 'string',
    ];
}
