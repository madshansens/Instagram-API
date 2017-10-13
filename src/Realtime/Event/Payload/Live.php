<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyMapper;

/**
 * Live.
 *
 * @method string getBroadcastId()
 * @method mixed getBroadcastMessage()
 * @method mixed getDisplayNotification()
 * @method mixed getIsPeriodic()
 * @method \InstagramAPI\Response\Model\User getUser()
 * @method bool isBroadcastId()
 * @method bool isBroadcastMessage()
 * @method bool isDisplayNotification()
 * @method bool isIsPeriodic()
 * @method bool isUser()
 * @method $this setBroadcastId(string $value)
 * @method $this setBroadcastMessage(mixed $value)
 * @method $this setDisplayNotification(mixed $value)
 * @method $this setIsPeriodic(mixed $value)
 * @method $this setUser(\InstagramAPI\Response\Model\User $value)
 * @method $this unsetBroadcastId()
 * @method $this unsetBroadcastMessage()
 * @method $this unsetDisplayNotification()
 * @method $this unsetIsPeriodic()
 * @method $this unsetUser()
 */
class Live extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user'                 => '\InstagramAPI\Response\Model\User',
        'broadcast_id'         => 'string',
        'is_periodic'          => '',
        'broadcast_message'    => '',
        'display_notification' => '',
    ];
}
