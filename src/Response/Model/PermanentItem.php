<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * PermanentItem.
 *
 * @method string getItemId()
 * @method mixed getItemType()
 * @method mixed getLike()
 * @method Link getLink()
 * @method Location getLocation()
 * @method MediaData getMedia()
 * @method Item getMediaShare()
 * @method ReelShare getReelShare()
 * @method string getText()
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
 * @method $this setItemId(string $value)
 * @method $this setItemType(mixed $value)
 * @method $this setLike(mixed $value)
 * @method $this setLink(Link $value)
 * @method $this setLocation(Location $value)
 * @method $this setMedia(MediaData $value)
 * @method $this setMediaShare(Item $value)
 * @method $this setReelShare(ReelShare $value)
 * @method $this setText(string $value)
 * @method $this setTimestamp(mixed $value)
 * @method $this setUserId(string $value)
 * @method $this unsetItemId()
 * @method $this unsetItemType()
 * @method $this unsetLike()
 * @method $this unsetLink()
 * @method $this unsetLocation()
 * @method $this unsetMedia()
 * @method $this unsetMediaShare()
 * @method $this unsetReelShare()
 * @method $this unsetText()
 * @method $this unsetTimestamp()
 * @method $this unsetUserId()
 */
class PermanentItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'item_id'     => 'string',
        'user_id'     => 'string',
        'timestamp'   => '',
        'item_type'   => '',
        'text'        => 'string',
        'location'    => 'Location',
        'like'        => '',
        'media'       => 'MediaData',
        'link'        => 'Link',
        'media_share' => 'Item',
        'reel_share'  => 'ReelShare',
    ];
}
