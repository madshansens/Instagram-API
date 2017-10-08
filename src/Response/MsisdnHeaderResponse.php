<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class MsisdnHeaderResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'phone_number' => 'string',
        'url'          => 'string',
    ];
}
