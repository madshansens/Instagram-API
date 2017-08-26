<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method Broadcast[] getBroadcasts()
 * @method mixed getCanReply()
 * @method mixed getCanReshare()
 * @method mixed getLastSeenBroadcastTs()
 * @method mixed getMuted()
 * @method string getPk()
 * @method mixed getRankedPosition()
 * @method mixed getSeenRankedPosition()
 * @method User getUser()
 * @method bool isBroadcasts()
 * @method bool isCanReply()
 * @method bool isCanReshare()
 * @method bool isLastSeenBroadcastTs()
 * @method bool isMuted()
 * @method bool isPk()
 * @method bool isRankedPosition()
 * @method bool isSeenRankedPosition()
 * @method bool isUser()
 * @method setBroadcasts(Broadcast[] $value)
 * @method setCanReply(mixed $value)
 * @method setCanReshare(mixed $value)
 * @method setLastSeenBroadcastTs(mixed $value)
 * @method setMuted(mixed $value)
 * @method setPk(string $value)
 * @method setRankedPosition(mixed $value)
 * @method setSeenRankedPosition(mixed $value)
 * @method setUser(User $value)
 */
class PostLiveItem extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $pk;
    /**
     * @var User
     */
    public $user;
    /**
     * @var Broadcast[]
     */
    public $broadcasts;
    public $last_seen_broadcast_ts;
    public $can_reply;
    public $ranked_position;
    public $seen_ranked_position;
    public $muted;
    public $can_reshare;
}
