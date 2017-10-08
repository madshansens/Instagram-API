<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class CommentBroadcastResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'comment' => 'Model\Comment',
    ];
}
