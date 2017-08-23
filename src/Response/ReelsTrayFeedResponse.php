<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method Model\Broadcast[] getBroadcasts()
 * @method mixed getFaceFilterNuxVersion()
 * @method Model\PostLive getPostLive()
 * @method mixed getStickerVersion()
 * @method mixed getStoryRankingToken()
 * @method Model\StoryTray[] getTray()
 * @method bool isBroadcasts()
 * @method bool isFaceFilterNuxVersion()
 * @method bool isPostLive()
 * @method bool isStickerVersion()
 * @method bool isStoryRankingToken()
 * @method bool isTray()
 * @method setBroadcasts(Model\Broadcast[] $value)
 * @method setFaceFilterNuxVersion(mixed $value)
 * @method setPostLive(Model\PostLive $value)
 * @method setStickerVersion(mixed $value)
 * @method setStoryRankingToken(mixed $value)
 * @method setTray(Model\StoryTray[] $value)
 */
class ReelsTrayFeedResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\StoryTray[]
     */
    public $tray;
    /**
     * @var Model\Broadcast[]
     */
    public $broadcasts;
    /**
     * @var Model\PostLive
     */
    public $post_live;
    public $sticker_version;
    public $face_filter_nux_version;
    public $story_ranking_token;
}
