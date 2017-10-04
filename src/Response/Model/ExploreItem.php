<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method Channel getChannel()
 * @method ExploreItemInfo getExploreItemInfo()
 * @method Item getMedia()
 * @method Stories getStories()
 * @method bool isChannel()
 * @method bool isExploreItemInfo()
 * @method bool isMedia()
 * @method bool isStories()
 * @method setChannel(Channel $value)
 * @method setExploreItemInfo(ExploreItemInfo $value)
 * @method setMedia(Item $value)
 * @method setStories(Stories $value)
 */
class ExploreItem extends AutoPropertyHandler
{
    /**
     * @var Item
     */
    public $media;
    /**
     * @var Stories
     */
    public $stories;
    /**
     * @var Channel
     */
    public $channel;
    /**
     * @var ExploreItemInfo
     */
    public $explore_item_info;
}
