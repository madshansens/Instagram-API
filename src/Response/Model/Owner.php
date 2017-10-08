<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Owner extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'type'                 => '',
        'pk'                   => 'string',
        'name'                 => '',
        'profile_pic_url'      => '',
        'profile_pic_username' => '',
        'short_name'           => '',
        'lat'                  => 'float',
        'lng'                  => 'float',
        'location_dict'        => 'Location',
    ];
}
