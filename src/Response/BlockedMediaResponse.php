<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class BlockedMediaResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $media_ids;
}
