<?php

namespace InstagramAPI\Response;

class AccountCreateResponse extends \InstagramAPI\Response
{
    public $account_created;
    /**
     * @var Model\User
     */
    public $created_user;
}
