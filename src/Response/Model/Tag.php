<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Tag extends AutoPropertyHandler
{
    public $media_count;
    public $name;
    /**
     * @var string
     */
    public $id;
}
