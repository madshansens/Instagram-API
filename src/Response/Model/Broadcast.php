<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getBroadcastMessage()
 * @method BroadcastOwner getBroadcastOwner()
 * @method mixed getBroadcastStatus()
 * @method mixed getCoverFrameUrl()
 * @method mixed getDashAbrPlaybackUrl()
 * @method mixed getDashManifest()
 * @method mixed getDashPlaybackUrl()
 * @method mixed getEncodingTag()
 * @method mixed getExpireAt()
 * @method string getId()
 * @method mixed getInternalOnly()
 * @method string getMediaId()
 * @method mixed getMuted()
 * @method mixed getNumberOfQualities()
 * @method mixed getOrganicTrackingToken()
 * @method mixed getPublishedTime()
 * @method mixed getRankedPosition()
 * @method mixed getRtmpPlaybackUrl()
 * @method mixed getSeenRankedPosition()
 * @method mixed getTotalUniqueViewerCount()
 * @method mixed getViewerCount()
 * @method bool isBroadcastMessage()
 * @method bool isBroadcastOwner()
 * @method bool isBroadcastStatus()
 * @method bool isCoverFrameUrl()
 * @method bool isDashAbrPlaybackUrl()
 * @method bool isDashManifest()
 * @method bool isDashPlaybackUrl()
 * @method bool isEncodingTag()
 * @method bool isExpireAt()
 * @method bool isId()
 * @method bool isInternalOnly()
 * @method bool isMediaId()
 * @method bool isMuted()
 * @method bool isNumberOfQualities()
 * @method bool isOrganicTrackingToken()
 * @method bool isPublishedTime()
 * @method bool isRankedPosition()
 * @method bool isRtmpPlaybackUrl()
 * @method bool isSeenRankedPosition()
 * @method bool isTotalUniqueViewerCount()
 * @method bool isViewerCount()
 * @method setBroadcastMessage(mixed $value)
 * @method setBroadcastOwner(BroadcastOwner $value)
 * @method setBroadcastStatus(mixed $value)
 * @method setCoverFrameUrl(mixed $value)
 * @method setDashAbrPlaybackUrl(mixed $value)
 * @method setDashManifest(mixed $value)
 * @method setDashPlaybackUrl(mixed $value)
 * @method setEncodingTag(mixed $value)
 * @method setExpireAt(mixed $value)
 * @method setId(string $value)
 * @method setInternalOnly(mixed $value)
 * @method setMediaId(string $value)
 * @method setMuted(mixed $value)
 * @method setNumberOfQualities(mixed $value)
 * @method setOrganicTrackingToken(mixed $value)
 * @method setPublishedTime(mixed $value)
 * @method setRankedPosition(mixed $value)
 * @method setRtmpPlaybackUrl(mixed $value)
 * @method setSeenRankedPosition(mixed $value)
 * @method setTotalUniqueViewerCount(mixed $value)
 * @method setViewerCount(mixed $value)
 */
class Broadcast extends AutoPropertyHandler
{
    /**
     * @var BroadcastOwner
     */
    public $broadcast_owner;
    public $broadcast_status;
    public $cover_frame_url;
    public $published_time;
    public $broadcast_message;
    public $muted;
    /**
     * @var string
     */
    public $media_id;
    /**
     * @var string
     */
    public $id;
    public $rtmp_playback_url;
    public $dash_abr_playback_url;
    public $dash_playback_url;
    public $ranked_position;
    public $organic_tracking_token;
    public $seen_ranked_position;
    public $viewer_count;
    public $dash_manifest;
    public $expire_at;
    public $encoding_tag;
    public $total_unique_viewer_count;
    public $internal_only;
    public $number_of_qualities;
}
