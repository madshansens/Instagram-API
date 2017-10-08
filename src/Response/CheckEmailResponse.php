<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class CheckEmailResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'valid'                => '',
        'available'            => '',
        'confirmed'            => '',
        'username_suggestions' => 'string[]',
        'error_type'           => '',
    ];
}
