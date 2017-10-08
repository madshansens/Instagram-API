<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class LinkAddressBookResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'items' => 'Model\Suggestion[]',
    ];
}
