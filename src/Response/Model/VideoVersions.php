<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class VideoVersions extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'url'    => '',
        'type'   => '',
        'width'  => '',
        'height' => '',
        'id'     => 'string',
    ];
}
