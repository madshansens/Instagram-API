<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class ExploreItem extends AutoPropertyHandler
{
    /**
     * @var Item
     */
    public $media;
    /**
     * @var Stories
     */
    public $stories;
    /**
     * @var Channel
     */
    public $channel;
}
