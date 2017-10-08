<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectRecentRecipientsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'expiration_interval' => '',
        'recent_recipients'   => '',
    ];
}
