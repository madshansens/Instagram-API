<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FacebookHiddenEntitiesResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'recent' => 'Model\HiddenEntities',
    ];
}
