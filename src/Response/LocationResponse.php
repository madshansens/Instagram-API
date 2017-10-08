<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class LocationResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'venues'     => 'Model\Location[]',
        'request_id' => 'string',
    ];
}
