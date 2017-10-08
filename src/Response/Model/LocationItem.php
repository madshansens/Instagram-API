<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class LocationItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media_bundles' => '',
        'subtitle'      => '',
        'location'      => 'Location',
        'title'         => '',
    ];
}
