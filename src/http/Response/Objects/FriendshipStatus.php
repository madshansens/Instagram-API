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
        if (isset($data['followed_by'])) {
            $this->followed_by = $data['followed_by'];
        }
        if (isset($data['is_blocking_reel'])) {
            $this->is_blocking_reel = $data['is_blocking_reel'];
        }
        if (isset($data['is_muting_reel'])) {
            $this->is_muting_reel = $data['is_muting_reel'];
        }
        if (isset($data['blocking'])) {
            $this->blocking = $data['blocking'];
        }
        if (array_key_exists('incoming_request', $data)) {
            $this->incoming_request = $data['incoming_request'];
            if ($this->incoming_request == 'followed_by') {
                $this->followed_by = true;
            }
        }
        if (array_key_exists('outgoing_request', $data)) {
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

    public function isPending()
    {
        return $this->outgoing_request == 'requested';
    }

    public function isPrivate()
    {
        return $this->is_private;
    }

    public function isBlockingReel()
    {
        return $this->is_blocking_reel;
    }

    public function isMutingReel()
    {
        return $this->is_muting_reel;
    }

    public function getBlocking()
    {
        return $this->blocking;
    }
}
