<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class DirectThreadLastSeenAt extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $item_id;
    public $timestamp;
}
