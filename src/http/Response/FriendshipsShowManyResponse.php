<?php

namespace InstagramAPI;

class FriendshipsShowManyResponse extends Response
{
    protected $friendships = [];

    public function __construct($response)
    {
        parent::__construct($response);

        if (isset($response['friendship_statuses'])) {
            foreach ($response['friendship_statuses'] as $user => $relationship) {
                $this->friendships[$user] = new FriendshipStatus($relationship);
            }
        }
    }

    /**
     * Lists Array of Friendships.
     *
     * @return array of Username => FriendshipStatus
     */
    public function getFriendships()
    {
        return $this->friendships;
    }
}
