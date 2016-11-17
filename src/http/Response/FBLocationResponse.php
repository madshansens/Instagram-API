<?php

namespace InstagramAPI;

class FBLocationResponse extends Response
{
    var $has_more;
    /** 
    * @var LocationItem[]
    */
    var $items;
}
