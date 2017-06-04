<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class MegaphoneLogResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $success;
}
