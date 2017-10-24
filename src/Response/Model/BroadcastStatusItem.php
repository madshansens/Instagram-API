<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * BroadcastStatusItem.
 *
 * @method mixed getBroadcastStatus()
 * @method string getCoverFrameUrl()
 * @method bool getHasReducedVisibility()
 * @method string getId()
 * @method mixed getViewerCount()
 * @method bool isBroadcastStatus()
 * @method bool isCoverFrameUrl()
 * @method bool isHasReducedVisibility()
 * @method bool isId()
 * @method bool isViewerCount()
 * @method $this setBroadcastStatus(mixed $value)
 * @method $this setCoverFrameUrl(string $value)
 * @method $this setHasReducedVisibility(bool $value)
 * @method $this setId(string $value)
 * @method $this setViewerCount(mixed $value)
 * @method $this unsetBroadcastStatus()
 * @method $this unsetCoverFrameUrl()
 * @method $this unsetHasReducedVisibility()
 * @method $this unsetId()
 * @method $this unsetViewerCount()
 */
class BroadcastStatusItem extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'broadcast_status'       => '',
        'has_reduced_visibility' => 'bool',
        'cover_frame_url'        => 'string',
        'viewer_count'           => '',
        'id'                     => 'string',
    ];
}
