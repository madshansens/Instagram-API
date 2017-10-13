<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Reel.
 *
 * @method Broadcast getBroadcast()
 * @method mixed getCanReply()
 * @method mixed getExpiringAt()
 * @method string getId()
 * @method Item[] getItems()
 * @method mixed getLatestReelMedia()
 * @method Location getLocation()
 * @method mixed getPrefetchCount()
 * @method mixed getSeen()
 * @method User getUser()
 * @method bool isBroadcast()
 * @method bool isCanReply()
 * @method bool isExpiringAt()
 * @method bool isId()
 * @method bool isItems()
 * @method bool isLatestReelMedia()
 * @method bool isLocation()
 * @method bool isPrefetchCount()
 * @method bool isSeen()
 * @method bool isUser()
 * @method $this setBroadcast(Broadcast $value)
 * @method $this setCanReply(mixed $value)
 * @method $this setExpiringAt(mixed $value)
 * @method $this setId(string $value)
 * @method $this setItems(Item[] $value)
 * @method $this setLatestReelMedia(mixed $value)
 * @method $this setLocation(Location $value)
 * @method $this setPrefetchCount(mixed $value)
 * @method $this setSeen(mixed $value)
 * @method $this setUser(User $value)
 * @method $this unsetBroadcast()
 * @method $this unsetCanReply()
 * @method $this unsetExpiringAt()
 * @method $this unsetId()
 * @method $this unsetItems()
 * @method $this unsetLatestReelMedia()
 * @method $this unsetLocation()
 * @method $this unsetPrefetchCount()
 * @method $this unsetSeen()
 * @method $this unsetUser()
 */
class Reel extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                => 'string',
        'items'             => 'Item[]',
        'user'              => 'User',
        'expiring_at'       => '',
        'seen'              => '',
        'can_reply'         => '',
        'location'          => 'Location',
        'latest_reel_media' => '',
        'prefetch_count'    => '',
        'broadcast'         => 'Broadcast',
    ];
}
