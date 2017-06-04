<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class FollowingRecentActivityResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\Story[]
     */
    public $stories;
    /**
     * @var string
     */
    public $next_max_id;
    public $auto_load_more_enabled;
    public $megaphone;
}
