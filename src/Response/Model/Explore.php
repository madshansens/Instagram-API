<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Explore extends AutoPropertyHandler
{
    public $explanation;
    /**
     * @var string
     */
    public $actor_id;
    public $source_token;
}
