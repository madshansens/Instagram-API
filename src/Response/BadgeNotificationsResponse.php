<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class BadgeNotificationsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        // Only exists if you have notifications, contains data keyed by userId:
        'badge_payload' => '',
    ];
}
