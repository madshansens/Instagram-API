<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class UploadVideoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'upload_id'          => 'string',
        'configure_delay_ms' => 'float',
        'result'             => '',
    ];
}
