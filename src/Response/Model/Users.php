<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Users extends AutoPropertyHandler
{
    public $position;
    /**
     * @var User
     */
    public $user;
}
