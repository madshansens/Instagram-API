<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class ImageCandidate extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'url'    => '',
        'width'  => '',
        'height' => '',
    ];
}
