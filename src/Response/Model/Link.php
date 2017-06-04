<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Link extends AutoPropertyHandler
{
    public $start;
    public $end;
    /**
     * @var string
     */
    public $id;
    public $type;
}
