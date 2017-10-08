<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * @method Model\Broadcast getBroadcast()
 * @method mixed getCanReply()
 * @method mixed getExpiringAt()
 * @method string getId()
 * @method Model\Item[] getItems()
 * @method mixed getLatestReelMedia()
 * @method Model\Location getLocation()
 * @method string getMessage()
 * @method mixed getPrefetchCount()
 * @method mixed getSeen()
 * @method string getStatus()
 * @method Model\User getUser()
 * @method Model\_Message[] get_Messages()
 * @method bool isBroadcast()
 * @method bool isCanReply()
 * @method bool isExpiringAt()
 * @method bool isId()
 * @method bool isItems()
 * @method bool isLatestReelMedia()
 * @method bool isLocation()
 * @method bool isMessage()
 * @method bool isPrefetchCount()
 * @method bool isSeen()
 * @method bool isStatus()
 * @method bool isUser()
 * @method bool is_Messages()
 * @method $this setBroadcast(Model\Broadcast $value)
 * @method $this setCanReply(mixed $value)
 * @method $this setExpiringAt(mixed $value)
 * @method $this setId(string $value)
 * @method $this setItems(Model\Item[] $value)
 * @method $this setLatestReelMedia(mixed $value)
 * @method $this setLocation(Model\Location $value)
 * @method $this setMessage(mixed $value)
 * @method $this setPrefetchCount(mixed $value)
 * @method $this setSeen(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this setUser(Model\User $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBroadcast()
 * @method $this unsetCanReply()
 * @method $this unsetExpiringAt()
 * @method $this unsetId()
 * @method $this unsetItems()
 * @method $this unsetLatestReelMedia()
 * @method $this unsetLocation()
 * @method $this unsetMessage()
 * @method $this unsetPrefetchCount()
 * @method $this unsetSeen()
 * @method $this unsetStatus()
 * @method $this unsetUser()
 * @method $this unset_Messages()
 */
class UserReelMediaFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        Model\Reel::class, // Import property map.
    ];
}
