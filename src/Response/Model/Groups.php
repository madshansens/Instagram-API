<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Groups extends AutoPropertyHandler
{
    public $type;
    /**
     * @var Item[]
     */
    public $items;
}
