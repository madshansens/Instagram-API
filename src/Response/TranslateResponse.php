<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class TranslateResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'comment_translations' => 'Model\CommentTranslations[]',
    ];
}
