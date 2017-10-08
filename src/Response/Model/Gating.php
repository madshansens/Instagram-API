<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Gating extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'gating_type' => '',
        'description' => '',
        'buttons'     => '',
        'title'       => '',
    ];
}
