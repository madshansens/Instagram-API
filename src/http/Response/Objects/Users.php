<?php

namespace InstagramAPI;

class Users
{
    protected $position;
    protected $user;

    public function __construct($users)
    {
        $this->position = $users['position'];
        $this->user = new User($users['user']);
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
