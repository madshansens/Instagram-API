<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class DirectSendItemResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $action;
    public $status_code;
    /** @var Model\DirectSendItemPayload */
    public $payload;
}
