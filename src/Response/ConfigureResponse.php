<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ConfigureResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'upload_id'         => 'string',
        'media'             => 'Model\Item',
        'client_sidecar_id' => 'string',
        'message_metadata'  => 'Model\DirectMessageMetadata[]',
    ];
}
