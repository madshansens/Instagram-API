<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class LocationItem extends AutoPropertyHandler
{
    public $media_bundles;
    public $subtitle;
    /**
     * @var Location
     */
    public $location;
    public $title;
}
