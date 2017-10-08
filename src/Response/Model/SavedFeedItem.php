<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class SavedFeedItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media' => 'Item',
    ];
}
