<?php

namespace InstagramAPI;

class Story extends Response
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
