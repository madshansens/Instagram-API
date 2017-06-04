<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class ProfileNoticeResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $has_change_password_megaphone;
}
