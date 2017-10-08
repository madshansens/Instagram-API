<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SearchTagResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'has_more'   => '',
        'results'    => 'Model\Tag[]',
        'rank_token' => '',
    ];
}
