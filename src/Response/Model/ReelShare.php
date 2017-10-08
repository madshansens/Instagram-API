<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class ReelShare extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'tray'                => 'Item[]',
        'story_ranking_token' => '',
        'broadcasts'          => '',
        'sticker_version'     => '',
        'text'                => '',
        'type'                => '',
        'media'               => 'Item',
        'mentioned_user_id'   => 'string',
    ];
}
