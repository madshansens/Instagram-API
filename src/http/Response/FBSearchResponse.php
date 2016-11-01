<?php

namespace InstagramAPI;

class FBSearchResponse extends Response
{
    protected $has_more;
    protected $hashtags;
    protected $users;
    protected $places;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->has_more = $response['has_more'];
            $this->hashtags = [];
            foreach ($response['hashtags'] as $hashtags) {
                $this->hashtags[] = new Hashtags($hashtags);
            }
            $this->users = [];
            foreach ($response['users'] as $users) {
                $this->users[] = new Users($users);
            }
            $this->places = [];
            foreach ($response['places'] as $places) {
                $this->places[] = new Place($places);
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

    public function getHashtags()
    {
        return $this->hashtags;
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function getPlaces()
    {
        return $this->places;
    }
}
