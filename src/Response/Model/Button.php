<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Button extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'text'             => '',
        'url'              => '',
        'action'           => '',
        'background_color' => '',
        'border_color'     => '',
        'text_color'       => '',
        'action_info'      => '',
    ];
}
