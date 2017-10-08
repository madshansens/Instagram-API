<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Usertag extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'in'           => 'In[]',
        'photo_of_you' => '',
    ];
}
