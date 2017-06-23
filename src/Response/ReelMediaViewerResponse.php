<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method string getNextMaxId()
 * @method mixed getTotalViewerCount()
 * @method mixed getUserCount()
 * @method Model\User[] getUsers()
 * @method bool isNextMaxId()
 * @method bool isTotalViewerCount()
 * @method bool isUserCount()
 * @method bool isUsers()
 * @method setNextMaxId(string $value)
 * @method setTotalViewerCount(mixed $value)
 * @method setUserCount(mixed $value)
 * @method setUsers(Model\User[] $value)
 */
class ReelMediaViewerResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\User[]
     */
    public $users;
    /**
     * @var string
     */
    public $next_max_id;
    public $user_count;
    public $total_viewer_count;
}
