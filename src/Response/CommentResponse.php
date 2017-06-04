<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class CommentResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $comment;
}
