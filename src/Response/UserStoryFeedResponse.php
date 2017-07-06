<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method Model\Broadcast getBroadcast()
 * @method Model\PostLiveItem getPostLiveItem()
 * @method Model\Reel getReel()
 * @method bool isBroadcast()
 * @method bool isPostLiveItem()
 * @method bool isReel()
 * @method setBroadcast(Model\Broadcast $value)
 * @method setPostLiveItem(Model\PostLiveItem $value)
 * @method setReel(Model\Reel $value)
 */
class UserStoryFeedResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\Broadcast
     */
    public $broadcast;
    /**
     * @var Model\Reel
     */
    public $reel;
    /**
     * @var Model\PostLiveItem
     */
    public $post_live_item;
}
