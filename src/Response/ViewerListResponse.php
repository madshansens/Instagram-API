<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ViewerListResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'users' => 'Model\User[]',
    ];
}
