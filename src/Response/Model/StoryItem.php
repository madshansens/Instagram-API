<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getCanReply()
 * @method mixed getExpiringAt()
 * @method mixed getId()
 * @method Item[] getItems()
 * @method mixed getLatestReelMedia()
 * @method Location getLocation()
 * @method Owner getOwner()
 * @method mixed getPrefetchCount()
 * @method mixed getSeen()
 * @method bool isCanReply()
 * @method bool isExpiringAt()
 * @method bool isId()
 * @method bool isItems()
 * @method bool isLatestReelMedia()
 * @method bool isLocation()
 * @method bool isOwner()
 * @method bool isPrefetchCount()
 * @method bool isSeen()
 * @method setCanReply(mixed $value)
 * @method setExpiringAt(mixed $value)
 * @method setId(mixed $value)
 * @method setItems(Item[] $value)
 * @method setLatestReelMedia(mixed $value)
 * @method setLocation(Location $value)
 * @method setOwner(Owner $value)
 * @method setPrefetchCount(mixed $value)
 * @method setSeen(mixed $value)
 */
class StoryItem extends AutoPropertyHandler
{
    public $id;
    public $latest_reel_media;
    public $expiring_at;
    public $seen;
    public $can_reply;
    /**
     * @var Owner
     */
    public $owner;
    /**
     * @var Item[]
     */
    public $items;
    /**
     * @var Location
     */
    public $location;
    public $prefetch_count;
}
