<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class GenericMegaphone extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'type'              => '',
        'title'             => '',
        'message'           => '',
        'dismissible'       => '',
        'icon'              => '',
        'buttons'           => 'Button[]',
        'megaphone_version' => '',
        'button_layout'     => '',
        'action_info'       => '',
        'button_location'   => '',
        'background_color'  => '',
        'title_color'       => '',
        'message_color'     => '',
        'uuid'              => 'string',
    ];
}
