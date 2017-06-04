<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class MediaLikersResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $user_count;
    /**
     * @var Model\User[]
     */
    public $users;
}
