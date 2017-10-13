<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * ReelsTrayFeedResponse.
 *
 * @method Model\Broadcast[] getBroadcasts()
 * @method mixed getFaceFilterNuxVersion()
 * @method mixed getMessage()
 * @method Model\PostLive getPostLive()
 * @method string getStatus()
 * @method mixed getStickerVersion()
 * @method mixed getStoryRankingToken()
 * @method Model\StoryTray[] getTray()
 * @method Model\_Message[] get_Messages()
 * @method bool isBroadcasts()
 * @method bool isFaceFilterNuxVersion()
 * @method bool isMessage()
 * @method bool isPostLive()
 * @method bool isStatus()
 * @method bool isStickerVersion()
 * @method bool isStoryRankingToken()
 * @method bool isTray()
 * @method bool is_Messages()
 * @method $this setBroadcasts(Model\Broadcast[] $value)
 * @method $this setFaceFilterNuxVersion(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setPostLive(Model\PostLive $value)
 * @method $this setStatus(string $value)
 * @method $this setStickerVersion(mixed $value)
 * @method $this setStoryRankingToken(mixed $value)
 * @method $this setTray(Model\StoryTray[] $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBroadcasts()
 * @method $this unsetFaceFilterNuxVersion()
 * @method $this unsetMessage()
 * @method $this unsetPostLive()
 * @method $this unsetStatus()
 * @method $this unsetStickerVersion()
 * @method $this unsetStoryRankingToken()
 * @method $this unsetTray()
 * @method $this unset_Messages()
 */
class ReelsTrayFeedResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'tray'                    => 'Model\StoryTray[]',
        'broadcasts'              => 'Model\Broadcast[]',
        'post_live'               => 'Model\PostLive',
        'sticker_version'         => '',
        'face_filter_nux_version' => '',
        'story_ranking_token'     => '',
    ];
}
