<?php

namespace InstagramAPI;

class ConfigureVideoResponse extends Response
{
    /**
     * @var string
     */
    public $upload_id;
    /**
     * @var string
     */
    public $media_id;
    public $image_url;
    public $video_version;
}
