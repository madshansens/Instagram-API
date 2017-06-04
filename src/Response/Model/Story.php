<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Story extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $pk;
    /**
     * @var Counts
     */
    public $counts;
    /**
     * @var Args
     */
    public $args;
    public $type;
}
