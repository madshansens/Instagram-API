<?php

namespace InstagramAPI;

class LocationResponse extends Response
{
    /**
     * @var Location[]
     */
    public $venues;
    public $request_id;
}
