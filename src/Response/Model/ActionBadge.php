<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class ActionBadge extends AutoPropertyHandler
{
    public $action_type;
    public $action_count;
    public $action_timestamp;
}
