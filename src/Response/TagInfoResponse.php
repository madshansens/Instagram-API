<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class TagInfoResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $profile;
    public $media_count;
}
