<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class BroadcastLikeCountResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $like_ts;
    public $likes;
    /**
     * @var User[]
     */
    public $likers;
}
