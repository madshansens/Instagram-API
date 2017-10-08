<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SuggestedUsersBadgeResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'should_badge'       => '',
        'new_suggestion_ids' => 'string[]',
    ];
}
