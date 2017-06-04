<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class In extends AutoPropertyHandler
{
    /*
     * @var Position
     */
    public $position;
    /*
     * @var User
     */
    public $user;
    public $time_in_video;
}
