<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * FriendshipsShowResponse.
 *
 * @method mixed getBlocking()
 * @method mixed getFollowedBy()
 * @method mixed getFollowing()
 * @method mixed getIncomingRequest()
 * @method mixed getIsBestie()
 * @method mixed getIsBlockingReel()
 * @method mixed getIsMutingReel()
 * @method bool getIsPrivate()
 * @method mixed getMessage()
 * @method mixed getOutgoingRequest()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isBlocking()
 * @method bool isFollowedBy()
 * @method bool isFollowing()
 * @method bool isIncomingRequest()
 * @method bool isIsBestie()
 * @method bool isIsBlockingReel()
 * @method bool isIsMutingReel()
 * @method bool isIsPrivate()
 * @method bool isMessage()
 * @method bool isOutgoingRequest()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setBlocking(mixed $value)
 * @method $this setFollowedBy(mixed $value)
 * @method $this setFollowing(mixed $value)
 * @method $this setIncomingRequest(mixed $value)
 * @method $this setIsBestie(mixed $value)
 * @method $this setIsBlockingReel(mixed $value)
 * @method $this setIsMutingReel(mixed $value)
 * @method $this setIsPrivate(bool $value)
 * @method $this setMessage(mixed $value)
 * @method $this setOutgoingRequest(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBlocking()
 * @method $this unsetFollowedBy()
 * @method $this unsetFollowing()
 * @method $this unsetIncomingRequest()
 * @method $this unsetIsBestie()
 * @method $this unsetIsBlockingReel()
 * @method $this unsetIsMutingReel()
 * @method $this unsetIsPrivate()
 * @method $this unsetMessage()
 * @method $this unsetOutgoingRequest()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class FriendshipsShowResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        Model\FriendshipStatus::class, // Import property map.
    ];
}
