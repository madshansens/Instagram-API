<?php

namespace InstagramAPI;

class FollowingResponse extends Response
{
    protected $followings;
    protected $next_max_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $users = [];
            foreach ($response['users'] as $user) {
                $users[] = new User($user);
            }
            $this->followings = $users;
            $this->next_max_id = $response['next_max_id'];
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getFollowings()
    {
        return $this->followings;
    }

    public function getNextMaxId()
    {
        return $this->next_max_id;
    }
}
