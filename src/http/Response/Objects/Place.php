<?php

namespace InstagramAPI;

class Place
{
    protected $position;
    protected $place;

    public function __construct($places)
    {
        $this->position = $places['position'];
        $this->place = new LocationItem($places['place']);
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getPlace()
    {
        return $this->place;
    }
}
