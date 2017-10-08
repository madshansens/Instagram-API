<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class RelatedLocationResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'related' => 'Model\Location[]',
    ];
}
