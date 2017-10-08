<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class CreateCollectionResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'collection_id'   => 'string',
        'collection_name' => '',
    ];
}
