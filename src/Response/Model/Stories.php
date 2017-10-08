<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Stories extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'is_portrait' => '',
        'tray'        => 'StoryTray[]',
        'id'          => 'string',
        'top_live'    => 'TopLive',
    ];
}
