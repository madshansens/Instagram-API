<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class AndroidLinks extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'linkType'          => '',
        'webUri'            => '',
        'androidClass'      => '',
        'package'           => '',
        'deeplinkUri'       => '',
        'callToActionTitle' => '',
        'redirectUri'       => '',
    ];
}
