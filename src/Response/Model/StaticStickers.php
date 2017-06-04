<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class StaticStickers extends AutoPropertyHandler
{
    public $include_in_recent;
    /**
     * @var string
     */
    public $id;
    /**
     * @var Stickers[]
     */
    public $stickers;
}
