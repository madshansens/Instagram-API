<?php

namespace InstagramAPI;

class SearchUserResponse extends Response
{
    protected $has_more;
    protected $num_results;
    protected $users;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->has_more = $response['has_more'];
            $this->num_results = $response['num_results'];
            $this->users = [];
            foreach ($response['users'] as $user) {
                $this->users[] = new User($user);
            }
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function hasMore()
    {
        return $this->has_more;
    }

    public function getNumResults()
    {
        return $this->num_results;
    }

    public function getUsers()
    {
        return $this->users;
    }
}
