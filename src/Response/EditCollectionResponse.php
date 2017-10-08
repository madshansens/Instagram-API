<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class EditCollectionResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'collection_id'   => 'string',
        'collection_name' => '',
    ];
}
