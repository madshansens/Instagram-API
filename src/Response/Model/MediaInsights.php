<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class MediaInsights extends AutoPropertyHandler
{
    /**
     * @var string[]
     */
    public $reach_count;
    public $impression_count;
    public $engagement_count;
}
