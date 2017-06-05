<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getActionCount()
 * @method mixed getActionTimestamp()
 * @method mixed getActionType()
 * @method bool isActionCount()
 * @method bool isActionTimestamp()
 * @method bool isActionType()
 * @method setActionCount(mixed $value)
 * @method setActionTimestamp(mixed $value)
 * @method setActionType(mixed $value)
 */
class ActionBadge extends AutoPropertyHandler
{
    public $action_type;
    public $action_count;
    public $action_timestamp;
}
