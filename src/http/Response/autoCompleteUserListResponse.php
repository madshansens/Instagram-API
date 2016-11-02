<?php

namespace InstagramAPI;

class autoCompleteUserListResponse extends Response
{
    protected $expires;
    protected $users;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->expires = $response['expires'];
            $users = [];
            foreach ($response['users'] as $user) {
                $users[] = new User($user);
            }
            $this->users = $users;
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @return User[]
     */
    public function getUsers()
    {
        return $this->users;
    }
}
