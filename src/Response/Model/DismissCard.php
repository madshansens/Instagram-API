<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class DismissCard extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'card_id'       => '',
        'image_url'     => '',
        'title'         => '',
        'message'       => '',
        'button_text'   => '',
        'camera_target' => '',
    ];
}
