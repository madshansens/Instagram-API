<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class SendConfirmEmailResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $title;
    public $is_email_legit;
    public $body;
}
