<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class CheckUsernameResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'username'   => '',
        'available'  => '',
        'error'      => '',
        'error_type' => '',
    ];
}
