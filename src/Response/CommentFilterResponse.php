<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class CommentFilterResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $config_value;
}
