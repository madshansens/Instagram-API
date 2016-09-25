<?php

namespace InstagramAPI;

class Link
{
    protected $start;
    protected $end;
    protected $id;
    protected $type;

    public function __construct($link)
    {
        $this->start = $link['start'];
        $this->end = $link['end'];
        $this->id = $link['id'];
        $this->type = $link['type'];
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }
}
