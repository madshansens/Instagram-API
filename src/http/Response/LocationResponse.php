<?php

namespace InstagramAPI;

class LocationResponse extends Response
{
    protected $venues;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $locations = [];
            foreach($response['venues'] as $location) {
                $locations[] = new Location($location);
            }
            $this->venues = $locations;
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getVenues()
    {
        return $this->venues;
    }
}
