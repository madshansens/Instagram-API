<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Experiment extends AutoPropertyHandler
{
    /**
     * @var Param[]
     */
    public $params;
    public $group;
    public $name;
}
