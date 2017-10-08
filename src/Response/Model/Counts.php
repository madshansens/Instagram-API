<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Counts extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'relationships'         => '',
        'requests'              => '',
        'photos_of_you'         => '',
        'usertags'              => '',
        'comments'              => '',
        'likes'                 => '',
        'comment_likes'         => '',
        'campaign_notification' => '',
    ];
}
