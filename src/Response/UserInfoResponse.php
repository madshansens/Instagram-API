<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class UserInfoResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $megaphone;
    /**
     * @var Model\User
     */
    public $user;
}
