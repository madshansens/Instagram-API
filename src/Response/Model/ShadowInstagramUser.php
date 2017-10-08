<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class ShadowInstagramUser extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'               => 'string',
        'username'         => '',
        'profile_picture'  => 'ProfilePicture',
        'business_manager' => 'BusinessManager',
        'error'            => '',
    ];
}
