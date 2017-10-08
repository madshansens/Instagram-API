<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class PushSettings extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'name'     => '',
        'eligible' => '',
        'title'    => '',
        'example'  => '',
        'options'  => '',
        'checked'  => '',
    ];
}
