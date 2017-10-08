<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class VideoUploadUrl extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'url'     => 'string',
        'job'     => 'string',
        'expires' => 'float',
    ];
}
