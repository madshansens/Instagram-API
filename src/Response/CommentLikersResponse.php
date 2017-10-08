<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class CommentLikersResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'users' => 'Model\User[]',
    ];
}
