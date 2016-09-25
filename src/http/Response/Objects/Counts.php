<?php

namespace InstagramAPI;

class Counts
{
    protected $relationships;
    protected $requests;
    protected $photos_of_you;

    public function __construct($counts)
    {
        if (array_key_exists('relationships', $counts)) {
            $this->relationships = $counts['relationships'];
        }
        if (array_key_exists('requests', $counts)) {
            $this->requests = $counts['requests'];
        }
        if (array_key_exists('photos_of_you', $counts)) {
            $this->photos_of_you = $counts['photos_of_you'];
        }
    }

    public function getRelationshipsCount()
    {
        return $this->relationships;
    }

    public function getRequestsCount()
    {
        return $this->requests;
    }

    public function getPhotosOfYouCount()
    {
        return $this->photos_of_you;
    }
}
