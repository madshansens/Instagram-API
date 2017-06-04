<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class SearchUserResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $has_more;
    public $num_results;
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var Model\User[]
     */
    public $users;
}
