<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class PermanentItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'item_id'     => 'string',
        'user_id'     => 'string',
        'timestamp'   => '',
        'item_type'   => '',
        'text'        => '',
        'location'    => 'Location',
        'like'        => '',
        'media'       => 'MediaData',
        'link'        => 'Link',
        'media_share' => 'Item',
        'reel_share'  => 'ReelShare',
    ];
}
