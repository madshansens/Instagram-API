<?php

namespace InstagramAPI;

class FriendshipStatus
{
    protected $following;
    protected $incoming_request;
    protected $outgoing_request;
    protected $is_private;

    public function __construct($data)
    {
        $this->following = $data['following'];
        $this->incoming_request = $data['incoming_request'];
        $this->outgoing_request = $data['outgoing_request'];
        $this->is_private = $data['is_private'];
    }

    public function getFollowing()
    {
        return $this->following;
    }

    public function getIncomingRequest()
    {
        return $this->incoming_request;
    }

    public function getOutgoingRequest()
    {
        return $this->outgoing_request;
    }

    public function isPrivate()
    {
        return $this->is_private;
    }
}
