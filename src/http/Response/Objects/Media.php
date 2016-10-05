<?php

namespace InstagramAPI;

class Media
{
    protected $image;
    protected $id;

    public function __construct($media)
    {
        $this->image = $media['image'];
        $this->id = $media['id'];
    }

    public function getMediaUrl()
    {
        return $this->image;
    }

    public function getMediaId()
    {
        return $this->id;
    }
}
