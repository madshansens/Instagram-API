<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class EditMediaResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'media' => 'Model\Item',
    ];
}
