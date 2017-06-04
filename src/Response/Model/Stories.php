<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Stories extends AutoPropertyHandler
{
    public $is_portrait;
    /**
     * @var Tray[]
     */
    public $tray;
    /**
     * @var string
     */
    public $id;
    /**
     * @var TopLive
     */
    public $top_live;
}
