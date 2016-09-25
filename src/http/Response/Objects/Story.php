<?php

namespace InstagramAPI;

class Story
{
    protected $pk;
    protected $counts;
    protected $args;
    protected $type;

    public function __construct($story)
    {
        $this->pk = $story['pk'];
        $this->counts = new Counts($story['counts']);
        $this->args = new Args($story['args']);
        $this->type = $story['type'];
    }

    public function getPk()
    {
        return $this->pk;
    }

    public function getCounts()
    {
        return $this->counts;
    }

    public function getArgs()
    {
        return $this->args;
    }

    public function getType()
    {
        return $this->type;
    }
}
