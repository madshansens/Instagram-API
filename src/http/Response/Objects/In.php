<?php

namespace InstagramAPI;

class In
{
    protected $position;
    protected $user;

    public function __construct($data)
    {
        $this->position = new Position($data['position']);
        $this->user = new User($data['user']);
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function getUser()
    {
        return $this->user;
    }
}
