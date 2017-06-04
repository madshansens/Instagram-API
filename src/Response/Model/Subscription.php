<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Subscription extends AutoPropertyHandler
{
    public $topic;
    public $url;
    public $sequence;
    public $auth;
}
