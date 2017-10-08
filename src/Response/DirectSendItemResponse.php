<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectSendItemResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'action'      => '',
        'status_code' => '',
        'payload'     => 'Model\DirectSendItemPayload',
    ];
}
