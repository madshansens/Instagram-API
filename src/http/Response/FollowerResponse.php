<?php

namespace InstagramAPI;

class FollowerResponse extends Response
{
    protected $followers;
    protected $next_max_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $users = [];
            foreach ($response['users'] as $user) {
                $users[] = new User($user);
            }
            $this->followers = $users;
            $this->next_max_id = isset($response['next_max_id']) ? $response['next_max_id'] : null;
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    /**
     * @return User
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    public function getNextMaxId()
    {
        return $this->next_max_id;
    }
}
