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

    /**
     * @return Position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
