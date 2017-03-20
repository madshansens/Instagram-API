<?php

namespace InstagramAPI;

class Stories extends Response
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
