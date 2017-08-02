<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method ActionLog getActionLog()
 * @method mixed getClientContext()
 * @method DirectExpiringSummary getExpiringMediaActionSummary()
 * @method mixed getHideInThread()
 * @method string getItemId()
 * @method mixed getItemType()
 * @method mixed getLike()
 * @method DirectLink getLink()
 * @method mixed getLiveVideoShare()
 * @method Location getLocation()
 * @method DirectThreadItemMedia getMedia()
 * @method Item getMediaShare()
 * @method Placeholder getPlaceholder()
 * @method Item getRavenMedia()
 * @method DirectReactions getReactions()
 * @method ReelShare getReelShare()
 * @method array getSeenUserIds()
 * @method mixed getText()
 * @method mixed getTimestamp()
 * @method string getUserId()
 * @method bool isActionLog()
 * @method bool isClientContext()
 * @method bool isExpiringMediaActionSummary()
 * @method bool isHideInThread()
 * @method bool isItemId()
 * @method bool isItemType()
 * @method bool isLike()
 * @method bool isLink()
 * @method bool isLiveVideoShare()
 * @method bool isLocation()
 * @method bool isMedia()
 * @method bool isMediaShare()
 * @method bool isPlaceholder()
 * @method bool isRavenMedia()
 * @method bool isReactions()
 * @method bool isReelShare()
 * @method bool isSeenUserIds()
 * @method bool isText()
 * @method bool isTimestamp()
 * @method bool isUserId()
 * @method setActionLog(ActionLog $value)
 * @method setClientContext(mixed $value)
 * @method setExpiringMediaActionSummary(DirectExpiringSummary $value)
 * @method setHideInThread(mixed $value)
 * @method setItemId(string $value)
 * @method setItemType(mixed $value)
 * @method setLike(mixed $value)
 * @method setLink(DirectLink $value)
 * @method setLiveVideoShare(mixed $value)
 * @method setLocation(Location $value)
 * @method setMedia(DirectThreadItemMedia $value)
 * @method setMediaShare(Item $value)
 * @method setPlaceholder(Placeholder $value)
 * @method setRavenMedia(Item $value)
 * @method setReactions(DirectReactions $value)
 * @method setReelShare(ReelShare $value)
 * @method setSeenUserIds(array $value)
 * @method setText(mixed $value)
 * @method setTimestamp(mixed $value)
 * @method setUserId(string $value)
 */
class DirectThreadItem extends AutoPropertyHandler
{
    const PLACEHOLDER = 'placeholder';
    const TEXT = 'text';
    const HASHTAG = 'hashtag';
    const LOCATION = 'location';
    const PROFILE = 'profile';
    const MEDIA = 'media';
    const MEDIA_SHARE = 'media_share';
    const EXPIRING_MEDIA = 'raven_media';
    const LIKE = 'like';
    const ACTION_LOG = 'action_log';
    const REACTION = 'reaction';
    const REEL_SHARE = 'reel_share';
    const LINK = 'link';

    /**
     * @var string
     */
    public $item_id;
    public $item_type;
    public $text;
    /**
     * @var Item
     */
    public $media_share;
    /**
     * @var DirectThreadItemMedia
     */
    public $media;
    /**
     * @var string
     */
    public $user_id;
    public $timestamp;
    public $client_context;
    public $hide_in_thread;
    /**
     * @var ActionLog
     */
    public $action_log;
    /**
     * @var DirectLink
     */
    public $link;
    /**
     * @var DirectReactions
     */
    public $reactions;
    /**
     * @var Item
     */
    public $raven_media;
    /**
     * @var array
     */
    public $seen_user_ids;
    /**
     * @var DirectExpiringSummary
     */
    public $expiring_media_action_summary;
    /**
     * @var ReelShare
     */
    public $reel_share;
    /**
     * @var Placeholder
     */
    public $placeholder;
    /**
     * @var Location
     */
    public $location;
    public $like;
    public $live_video_share;
}
