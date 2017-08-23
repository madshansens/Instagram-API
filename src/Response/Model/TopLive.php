<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method BroadcastOwner[] getBroadcastOwners()
 * @method mixed getRankedPosition()
 * @method bool isBroadcastOwners()
 * @method bool isRankedPosition()
 * @method setBroadcastOwners(BroadcastOwner[] $value)
 * @method setRankedPosition(mixed $value)
 */
class TopLive extends AutoPropertyHandler
{
    /**
     * @var BroadcastOwner[]
     */
    public $broadcast_owners;
    public $ranked_position;
}
