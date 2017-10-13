<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * TopLive.
 *
 * @method BroadcastOwner[] getBroadcastOwners()
 * @method mixed getRankedPosition()
 * @method bool isBroadcastOwners()
 * @method bool isRankedPosition()
 * @method $this setBroadcastOwners(BroadcastOwner[] $value)
 * @method $this setRankedPosition(mixed $value)
 * @method $this unsetBroadcastOwners()
 * @method $this unsetRankedPosition()
 */
class TopLive extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'broadcast_owners' => 'BroadcastOwner[]',
        'ranked_position'  => '',
    ];
}
