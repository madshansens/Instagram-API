<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class DirectSeenItemResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $action;
    /** @var Model\DirectSeenItemPayload */
    public $payload; // this is the number of unseen items
}
