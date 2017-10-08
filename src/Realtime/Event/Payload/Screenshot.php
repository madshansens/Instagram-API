<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyMapper;

/**
 * @method \InstagramAPI\Response\Model\User getActionUserDict()
 * @method mixed getMediaType()
 * @method bool isActionUserDict()
 * @method bool isMediaType()
 * @method $this setActionUserDict(\InstagramAPI\Response\Model\User $value)
 * @method $this setMediaType(mixed $value)
 * @method $this unsetActionUserDict()
 * @method $this unsetMediaType()
 */
class Screenshot extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'action_user_dict' => '\InstagramAPI\Response\Model\User',
        'media_type'       => '',
    ];
}
