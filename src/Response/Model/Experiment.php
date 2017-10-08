<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Experiment extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'params' => 'Param[]',
        'group'  => '',
        'name'   => '',
    ];
}
