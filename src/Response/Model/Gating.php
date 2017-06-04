<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Gating extends AutoPropertyHandler
{
    public $gating_type;
    public $description;
    public $buttons;
    public $title;
}
