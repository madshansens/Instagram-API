<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * FriendshipStatus.
 *
 * @method mixed getBlocking()
 * @method mixed getFollowedBy()
 * @method mixed getFollowing()
 * @method mixed getIncomingRequest()
 * @method mixed getIsBestie()
 * @method mixed getIsBlockingReel()
 * @method mixed getIsMutingReel()
 * @method bool getIsPrivate()
 * @method mixed getOutgoingRequest()
 * @method bool isBlocking()
 * @method bool isFollowedBy()
 * @method bool isFollowing()
 * @method bool isIncomingRequest()
 * @method bool isIsBestie()
 * @method bool isIsBlockingReel()
 * @method bool isIsMutingReel()
 * @method bool isIsPrivate()
 * @method bool isOutgoingRequest()
 * @method $this setBlocking(mixed $value)
 * @method $this setFollowedBy(mixed $value)
 * @method $this setFollowing(mixed $value)
 * @method $this setIncomingRequest(mixed $value)
 * @method $this setIsBestie(mixed $value)
 * @method $this setIsBlockingReel(mixed $value)
 * @method $this setIsMutingReel(mixed $value)
 * @method $this setIsPrivate(bool $value)
 * @method $this setOutgoingRequest(mixed $value)
 * @method $this unsetBlocking()
 * @method $this unsetFollowedBy()
 * @method $this unsetFollowing()
 * @method $this unsetIncomingRequest()
 * @method $this unsetIsBestie()
 * @method $this unsetIsBlockingReel()
 * @method $this unsetIsMutingReel()
 * @method $this unsetIsPrivate()
 * @method $this unsetOutgoingRequest()
 */
class FriendshipStatus extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'following'        => '',
        'followed_by'      => '',
        'incoming_request' => '',
        'outgoing_request' => '',
        'is_private'       => 'bool',
        'is_blocking_reel' => '',
        'is_muting_reel'   => '',
        'blocking'         => '',
        'is_bestie'        => '',
    ];
}
