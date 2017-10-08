<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Image_Versions2 extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'candidates'  => 'ImageCandidate[]',
        'trace_token' => '',
    ];
}
