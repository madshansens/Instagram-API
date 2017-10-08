<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class QueryResponse extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'shadow_instagram_user' => 'ShadowInstagramUser',
    ];
}
