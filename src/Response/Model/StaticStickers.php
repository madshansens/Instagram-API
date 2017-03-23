<?php

namespace InstagramAPI\Response\Model;

class StaticStickers extends \InstagramAPI\Response
{
    public $include_in_recent;
    public $id;
    /**
     * @var Stickers[]
     */
    public $stickers;
}
