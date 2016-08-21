<?php

namespace InstagramAPI;

class autoCompleteUserListResponse extends Response
{
    protected $expires;
    protected $users;

    public function __construct($response)
    {
        $this->expires = $response['expires'];
        $users = [];
        foreach($response['users'] as $user) {
            $users[] = new User($user);
        }
        $this->users = $users;
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function getUsers()
    {
        return $this->users;
    }
}
