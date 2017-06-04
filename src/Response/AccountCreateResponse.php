<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class AccountCreateResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $account_created;
    /**
     * @var Model\User
     */
    public $created_user;
}
