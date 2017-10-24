<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Broadcast.
 *
 * @method mixed getBroadcastMessage()
 * @method BroadcastOwner getBroadcastOwner()
 * @method mixed getBroadcastStatus()
 * @method string getCoverFrameUrl()
 * @method string getDashAbrPlaybackUrl()
 * @method mixed getDashManifest()
 * @method string getDashPlaybackUrl()
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
 * @method string getRtmpPlaybackUrl()
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
 * @method $this setBroadcastMessage(mixed $value)
 * @method $this setBroadcastOwner(BroadcastOwner $value)
 * @method $this setBroadcastStatus(mixed $value)
 * @method $this setCoverFrameUrl(string $value)
 * @method $this setDashAbrPlaybackUrl(string $value)
 * @method $this setDashManifest(mixed $value)
 * @method $this setDashPlaybackUrl(string $value)
 * @method $this setEncodingTag(mixed $value)
 * @method $this setExpireAt(mixed $value)
 * @method $this setId(string $value)
 * @method $this setInternalOnly(mixed $value)
 * @method $this setMediaId(string $value)
 * @method $this setMuted(mixed $value)
 * @method $this setNumberOfQualities(mixed $value)
 * @method $this setOrganicTrackingToken(mixed $value)
 * @method $this setPublishedTime(mixed $value)
 * @method $this setRankedPosition(mixed $value)
 * @method $this setRtmpPlaybackUrl(string $value)
 * @method $this setSeenRankedPosition(mixed $value)
 * @method $this setTotalUniqueViewerCount(mixed $value)
 * @method $this setViewerCount(mixed $value)
 * @method $this unsetBroadcastMessage()
 * @method $this unsetBroadcastOwner()
 * @method $this unsetBroadcastStatus()
 * @method $this unsetCoverFrameUrl()
 * @method $this unsetDashAbrPlaybackUrl()
 * @method $this unsetDashManifest()
 * @method $this unsetDashPlaybackUrl()
 * @method $this unsetEncodingTag()
 * @method $this unsetExpireAt()
 * @method $this unsetId()
 * @method $this unsetInternalOnly()
 * @method $this unsetMediaId()
 * @method $this unsetMuted()
 * @method $this unsetNumberOfQualities()
 * @method $this unsetOrganicTrackingToken()
 * @method $this unsetPublishedTime()
 * @method $this unsetRankedPosition()
 * @method $this unsetRtmpPlaybackUrl()
 * @method $this unsetSeenRankedPosition()
 * @method $this unsetTotalUniqueViewerCount()
 * @method $this unsetViewerCount()
 */
class Broadcast extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'broadcast_owner'           => 'BroadcastOwner',
        'broadcast_status'          => '',
        'cover_frame_url'           => 'string',
        'published_time'            => '',
        'broadcast_message'         => '',
        'muted'                     => '',
        'media_id'                  => 'string',
        'id'                        => 'string',
        'rtmp_playback_url'         => 'string',
        'dash_abr_playback_url'     => 'string',
        'dash_playback_url'         => 'string',
        'ranked_position'           => '',
        'organic_tracking_token'    => '',
        'seen_ranked_position'      => '',
        'viewer_count'              => '',
        'dash_manifest'             => '',
        'expire_at'                 => '',
        'encoding_tag'              => '',
        'total_unique_viewer_count' => '',
        'internal_only'             => '',
        'number_of_qualities'       => '',
    ];
}
