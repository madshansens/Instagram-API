<?php

namespace InstagramAPI;

class FollowingResponse extends Response
{
    protected $followings;
    protected $next_max_id;
    protected $big_list;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $users = [];
            foreach ($response['users'] as $user) {
                $users[] = new User($user);
            }
            $this->followings = $users;
            $this->big_list = $response['big_list'];
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
    public function getFollowings()
    {
        return $this->followings;
    }

    public function getNextMaxId()
    {
        return $this->next_max_id;
    }

    public function isBigList()
    {
        return $this->big_list;
    }
}
