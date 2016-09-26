<?php

namespace InstagramAPI;

class Hashtag
{
    protected $media_count;
    protected $name;
    protected $id;

    public function __construct($hashtag)
    {
        $this->media_count = $hashtag['media_count'];
        $this->name = $hashtag['name'];
        $this->id = $hashtag['id'];
    }

    public function getMediaCount()
    {
        return $this->media_count;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }
}
