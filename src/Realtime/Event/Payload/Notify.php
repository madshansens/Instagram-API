<?php

namespace InstagramAPI\Realtime\Event\Payload;

use InstagramAPI\AutoPropertyMapper;

/**
 * Notify.
 *
 * @method \InstagramAPI\Response\Model\ActionLog getActionLog()
 * @method string getUserId()
 * @method bool isActionLog()
 * @method bool isUserId()
 * @method $this setActionLog(\InstagramAPI\Response\Model\ActionLog $value)
 * @method $this setUserId(string $value)
 * @method $this unsetActionLog()
 * @method $this unsetUserId()
 */
class Notify extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'user_id'    => 'string',
        'action_log' => '\InstagramAPI\Response\Model\ActionLog',
    ];
}
