<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FacebookOTAResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'bundles'    => '',
        'request_id' => 'string',
    ];
}
