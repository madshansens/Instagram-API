<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method Model\User[] getUsers()
 * @method bool isUsers()
 * @method setUsers(Model\User[] $value)
 */
class CommentLikersResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\User[]
     */
    public $users;
}
