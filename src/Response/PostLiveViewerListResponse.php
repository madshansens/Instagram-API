<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getNextMaxId()
 * @method mixed getTotalViewerCount()
 * @method Model\User[] getUsers()
 * @method bool isNextMaxId()
 * @method bool isTotalViewerCount()
 * @method bool isUsers()
 * @method setNextMaxId(mixed $value)
 * @method setTotalViewerCount(mixed $value)
 * @method setUsers(Model\User[] $value)
 */
class PostLiveViewerListResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\User[]
     */
    public $users;
    public $next_max_id;
    public $total_viewer_count;
}
