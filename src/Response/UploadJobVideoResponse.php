<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class UploadJobVideoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'upload_id'         => 'string',
        'video_upload_urls' => 'Model\VideoUploadUrl[]',
    ];
}
