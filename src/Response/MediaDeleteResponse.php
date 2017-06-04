<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class MediaDeleteResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $did_delete;
}
