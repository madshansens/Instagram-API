<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method PostLiveItem[] getPostLiveItems()
 * @method bool isPostLiveItems()
 * @method setPostLiveItems(PostLiveItem[] $value)
 */
class PostLive extends AutoPropertyHandler
{
    /**
     * @var PostLiveItem[]
     */
    public $post_live_items;
}
