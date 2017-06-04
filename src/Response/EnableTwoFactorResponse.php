<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class EnableTwoFactorResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $backup_codes;
}
