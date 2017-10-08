<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class UploadPhotoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'upload_id' => 'string',
        'media_id'  => 'string',
    ];
}
