<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class StoryLocation extends AutoPropertyHandler
{
    /**
     * @var float
     */
    public $rotation;
    /**
     * @var float
     */
    public $x;
    /**
     * @var float
     */
    public $y;
    /**
     * @var float
     */
    public $height;
    /**
     * @var float
     */
    public $width;
    /**
     * @var Location
     */
    public $location;
}
