<?php

namespace InstagramAPI\Response\Model;

class InlineFollow extends \InstagramAPI\Response
{
    /**
     * @var User
     */
    public $user_info;
    public $following;
    public $outgoing_request;
}
