<?php

namespace InstagramAPI;

class FollowingRecentActivityResponse extends Response
{
    protected $stories;
    protected $next_max_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->stories = [];
            foreach ($response['stories'] as $story) {
                $this->stories[] = new Story($story);
            }

            $this->next_max_id = isset($response['next_max_id']) ? $response['next_max_id'] : null;
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getStories()
    {
        return $this->stories;
    }

    public function getNextMaxId()
    {
        return $this->next_max_id;
    }
}
