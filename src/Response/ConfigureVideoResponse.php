<?php

namespace InstagramAPI\Response;

class ConfigureVideoResponse extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $upload_id;
    /**
     * @var Model\Item
     */
    public $media;
}
