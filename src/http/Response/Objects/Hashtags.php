<?php

namespace InstagramAPI;

class Hashtags
{
    protected $position;
    protected $hashtag;

    public function __construct($hashtags)
    {
        $this->position = $hashtags['position'];
        $this->hashtag = new Hashtag($hashtags['hashtag']);
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getHashtag()
    {
        return $this->hashtag;
    }
}
