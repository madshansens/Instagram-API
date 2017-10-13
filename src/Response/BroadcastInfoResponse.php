<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * BroadcastInfoResponse.
 *
 * @method mixed getBroadcastMessage()
 * @method Model\User getBroadcastOwner()
 * @method mixed getBroadcastStatus()
 * @method string getId()
 * @method string getMediaId()
 * @method mixed getMessage()
 * @method mixed getOrganicTrackingToken()
 * @method mixed getPublishedTime()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isBroadcastMessage()
 * @method bool isBroadcastOwner()
 * @method bool isBroadcastStatus()
 * @method bool isId()
 * @method bool isMediaId()
 * @method bool isMessage()
 * @method bool isOrganicTrackingToken()
 * @method bool isPublishedTime()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setBroadcastMessage(mixed $value)
 * @method $this setBroadcastOwner(Model\User $value)
 * @method $this setBroadcastStatus(mixed $value)
 * @method $this setId(string $value)
 * @method $this setMediaId(string $value)
 * @method $this setMessage(mixed $value)
 * @method $this setOrganicTrackingToken(mixed $value)
 * @method $this setPublishedTime(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBroadcastMessage()
 * @method $this unsetBroadcastOwner()
 * @method $this unsetBroadcastStatus()
 * @method $this unsetId()
 * @method $this unsetMediaId()
 * @method $this unsetMessage()
 * @method $this unsetOrganicTrackingToken()
 * @method $this unsetPublishedTime()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class BroadcastInfoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'id'                     => 'string',
        'broadcast_message'      => '',
        'organic_tracking_token' => '',
        'published_time'         => '',
        'broadcast_status'       => '',
        'media_id'               => 'string',
        'broadcast_owner'        => 'Model\User',
    ];
}
