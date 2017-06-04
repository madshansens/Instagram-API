<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class CommentFilterKeywordsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $keywords;
}
