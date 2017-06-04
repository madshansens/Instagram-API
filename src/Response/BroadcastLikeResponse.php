<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class BroadcastLikeResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $likes;
}
