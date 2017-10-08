<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ReelSettingsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'message_prefs' => '',
        'blocked_reels' => 'Model\BlockedReels',
    ];
}
