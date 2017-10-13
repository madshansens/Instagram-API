<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * BroadcastItem.
 *
 * @method mixed getBroadcastMessage()
 * @method User getBroadcastOwner()
 * @method mixed getBroadcastStatus()
 * @method mixed getCoverFrameUrl()
 * @method mixed getDashAbrPlaybackUrl()
 * @method mixed getDashPlaybackUrl()
 * @method string getId()
 * @method string getMediaId()
 * @method mixed getOrganicTrackingToken()
 * @method mixed getPublishedTime()
 * @method mixed getRtmpPlaybackUrl()
 * @method mixed getViewerCount()
 * @method bool isBroadcastMessage()
 * @method bool isBroadcastOwner()
 * @method bool isBroadcastStatus()
 * @method bool isCoverFrameUrl()
 * @method bool isDashAbrPlaybackUrl()
 * @method bool isDashPlaybackUrl()
 * @method bool isId()
 * @method bool isMediaId()
 * @method bool isOrganicTrackingToken()
 * @method bool isPublishedTime()
 * @method bool isRtmpPlaybackUrl()
 * @method bool isViewerCount()
 * @method $this setBroadcastMessage(mixed $value)
 * @method $this setBroadcastOwner(User $value)
 * @method $this setBroadcastStatus(mixed $value)
 * @method $this setCoverFrameUrl(mixed $value)
 * @method $this setDashAbrPlaybackUrl(mixed $value)
 * @method $this setDashPlaybackUrl(mixed $value)
 * @method $this setId(string $value)
 * @method $this setMediaId(string $value)
 * @method $this setOrganicTrackingToken(mixed $value)
 * @method $this setPublishedTime(mixed $value)
 * @method $this setRtmpPlaybackUrl(mixed $value)
 * @method $this setViewerCount(mixed $value)
 * @method $this unsetBroadcastMessage()
 * @method $this unsetBroadcastOwner()
 * @method $this unsetBroadcastStatus()
 * @method $this unsetCoverFrameUrl()
 * @method $this unsetDashAbrPlaybackUrl()
 * @method $this unsetDashPlaybackUrl()
 * @method $this unsetId()
 * @method $this unsetMediaId()
 * @method $this unsetOrganicTrackingToken()
 * @method $this unsetPublishedTime()
 * @method $this unsetRtmpPlaybackUrl()
 * @method $this unsetViewerCount()
 */
class BroadcastItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'organic_tracking_token' => '',
        'published_time'         => '',
        'id'                     => 'string',
        'rtmp_playback_url'      => '',
        'cover_frame_url'        => '',
        'broadcast_status'       => '',
        'media_id'               => 'string',
        'broadcast_message'      => '',
        'viewer_count'           => '',
        'dash_abr_playback_url'  => '',
        'dash_playback_url'      => '',
        'broadcast_owner'        => 'User',
    ];
}
