<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class InlineFollow extends AutoPropertyHandler
{
    /**
     * @var User
     */
    public $user_info;
    public $following;
    public $outgoing_request;
}
