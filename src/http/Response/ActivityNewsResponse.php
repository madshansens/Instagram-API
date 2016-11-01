<?php

namespace InstagramAPI;

class ActivityNewsResponse extends Response
{
    protected $new_stories;
    protected $old_stories;
    protected $continuation;
    protected $friend_request_stories;
    protected $counts;
    protected $subscription;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->new_stories = [];
            foreach ($response['new_stories'] as $story) {
                $this->new_stories[] = new Story($story);
            }
            $this->old_stories = [];
            foreach ($response['old_stories'] as $story) {
                $this->old_stories[] = new Story($story);
            }
            if (array_key_exists('continuation', $response)) {
                $this->continuation = $response['continuation'];
            }
            $this->friend_request_stories = $response['friend_request_stories'];
            $this->counts = new Counts($response['counts']);
            $this->subscription = $response['subscription'];
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getNewStories()
    {
        return $this->new_stories;
    }

    public function getOldStories()
    {
        return $this->old_stories;
    }

    public function getContinuation()
    {
        return $this->continuation;
    }

    public function getFriendRequestStories()
    {
        return $this->friend_request_stories;
    }

    public function getCounts()
    {
        return $this->counts;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }
}
