<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class CommentTranslations extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'          => 'string',
        'translation' => '',
    ];
}
