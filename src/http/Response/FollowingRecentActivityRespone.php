<?php

namespace InstagramAPI;

class FollowingRecentActivityResponse extends Response
{
    protected $stories;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->stories = [];
            foreach ($response['stories'] as $story) {
                $this->stories[] = new Story($story);
            }
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getStories()
    {
        return $this->stories;
    }
}
