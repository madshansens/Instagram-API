<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectThreadResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'thread' => 'Model\DirectThread',
    ];
}
