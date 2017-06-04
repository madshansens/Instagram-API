<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class AutoCompleteUserListResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $expires;
    /**
     * @var Model\User[]
     */
    public $users;
}
