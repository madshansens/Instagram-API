<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class DirectRecentRecipientsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $expiration_interval;
    public $recent_recipients;
}
