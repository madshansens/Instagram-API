<?php

namespace InstagramAPI;

class FBLocationResponse extends Response
{
    public $has_more;
    /**
     * @var LocationItem[]
     */
    public $items;
}
