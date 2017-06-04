<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class DirectRankedRecipient extends AutoPropertyHandler
{
    /**
     * @var DirectThread
     */
    public $thread;
    /**
     * @var User
     */
    public $user;
}
