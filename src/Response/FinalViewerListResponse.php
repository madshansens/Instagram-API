<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getTotalUniqueViewerCount()
 * @method Model\User[] getUsers()
 * @method bool isTotalUniqueViewerCount()
 * @method bool isUsers()
 * @method setTotalUniqueViewerCount(mixed $value)
 * @method setUsers(Model\User[] $value)
 */
class FinalViewerListResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\User[]
     */
    public $users;
    public $total_unique_viewer_count;
}
