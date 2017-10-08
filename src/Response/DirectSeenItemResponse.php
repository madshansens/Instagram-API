<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectSeenItemResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'action'  => '',
        'payload' => 'Model\DirectSeenItemPayload', // The number of unseen items.
    ];
}
