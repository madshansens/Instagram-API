<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * @method mixed getBadgePayload()
 * @method string getMessage()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isBadgePayload()
 * @method bool isMessage()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setBadgePayload(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBadgePayload()
 * @method $this unsetMessage()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class BadgeNotificationsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        // Only exists if you have notifications, contains data keyed by userId:
        'badge_payload' => '',
    ];
}
