<?php

namespace InstagramAPI;

class VideoVersions
{
    protected $url;
    protected $type;
    protected $width;
    protected $height;

    public function __construct($response)
    {
        $this->url = $response['url'];
        $this->type = $response['type'];
        $this->width = $response['width'];
        $this->height = $response['height'];
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }
}
