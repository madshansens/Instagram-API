<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Stickers extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $id;
    public $tray_image_width_ratio;
    public $image_height;
    public $image_width_ratio;
    public $type;
    public $image_width;
    public $name;
    public $image_url;
}
