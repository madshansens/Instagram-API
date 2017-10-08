<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class ActionBadge extends AutoPropertyMapper
{
    const DELIVERED = 'raven_delivered';
    const SENT = 'raven_sent';
    const OPENED = 'raven_opened';
    const SCREENSHOT = 'raven_screenshot';
    const REPLAYED = 'raven_replayed';
    const CANNOT_DELIVER = 'raven_cannot_deliver';
    const SENDING = 'raven_sending';
    const BLOCKED = 'raven_blocked';
    const UNKNOWN = 'raven_unknown';
    const SUGGESTED = 'raven_suggested';

    const JSON_PROPERTY_MAP = [
        'action_type'      => '',
        'action_count'     => '',
        'action_timestamp' => '',
    ];
}
