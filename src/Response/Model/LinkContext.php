<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class LinkContext extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'link_url'       => 'string',
        'link_title'     => 'string',
        'link_summary'   => 'string',
        'link_image_url' => 'string',
    ];
}
