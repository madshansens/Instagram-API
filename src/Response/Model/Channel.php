<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Channel extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $channel_id;
    public $channel_type;
    public $title;
    public $header;
    public $media_count;
    /**
     * @var Item
     */
    public $media;
    public $context;
}
