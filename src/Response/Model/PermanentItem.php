<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method string getItemId()
 * @method mixed getItemType()
 * @method mixed getLike()
 * @method Link getLink()
 * @method Location getLocation()
 * @method MediaData getMedia()
 * @method Item getMediaShare()
 * @method ReelShare getReelShare()
 * @method mixed getText()
 * @method mixed getTimestamp()
 * @method string getUserId()
 * @method bool isItemId()
 * @method bool isItemType()
 * @method bool isLike()
 * @method bool isLink()
 * @method bool isLocation()
 * @method bool isMedia()
 * @method bool isMediaShare()
 * @method bool isReelShare()
 * @method bool isText()
 * @method bool isTimestamp()
 * @method bool isUserId()
 * @method setItemId(string $value)
 * @method setItemType(mixed $value)
 * @method setLike(mixed $value)
 * @method setLink(Link $value)
 * @method setLocation(Location $value)
 * @method setMedia(MediaData $value)
 * @method setMediaShare(Item $value)
 * @method setReelShare(ReelShare $value)
 * @method setText(mixed $value)
 * @method setTimestamp(mixed $value)
 * @method setUserId(string $value)
 */
class PermanentItem extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $item_id;
    /**
     * @var string
     */
    public $user_id;
    public $timestamp;
    public $item_type;
    public $text;
    /**
     * @var Location
     */
    public $location;
    public $like;
    /**
     * @var MediaData
     */
    public $media;
    /**
     * @var Link
     */
    public $link;
    /**
     * @var Item
     */
    public $media_share;
    /**
     * @var ReelShare
     */
    public $reel_share;
}
