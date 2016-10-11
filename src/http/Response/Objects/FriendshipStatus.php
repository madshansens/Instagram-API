<?php

namespace InstagramAPI;

class FriendshipStatus
{
    protected $following;
    protected $followed_by;
    protected $incoming_request;
    protected $outgoing_request;
    protected $is_private;
    protected $is_blocking_reel;
    protected $is_muting_reel;
    protected $blocking;


    public function __construct($data)
    {
        $this->following = $data['following'];
        $this->followed_by = $data['followed_by'];
        $this->is_blocking_reel = $data['is_blocking_reel'];
        $this->$is_muting_reel = $data['$is_muting_reel'];
        $this->$blocking = $data['$blocking'];
        if (array_key_exists('source_token', $data)) {
            $this->incoming_request = $data['incoming_request'];
        }
        if (array_key_exists('source_token', $data)) {
            $this->outgoing_request = $data['outgoing_request'];
        }
        if (array_key_exists('is_private', $data)) {
            $this->is_private = $data['is_private'];
        }
    }

    public function getFollowing()
    {
        return $this->following;
    }

    public function getFollowedBy()
    {
        return $this->followed_by;
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
    
    public function isBlockingReel()
    {
        return $this->$is_blocking_reel;
    }
    
    public function isMutingReel()
    {
        return $this->$is_muting_reel;
    }
    
    public function getBlocking()
    {
        return $this->$blocking;
    }

}
