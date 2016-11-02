<?php

namespace InstagramAPI;

class LocationResponse extends Response
{
    protected $venues;
    protected $request_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $locations = [];
            foreach ($response['venues'] as $location) {
                $locations[] = new Location($location);
            }
            $this->venues = $locations;
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    /**
     * @return Location[]
     */
    public function getVenues()
    {
        return $this->venues;
    }

    public function getRequestId()
    {
        return $this->request_id;
    }
}
