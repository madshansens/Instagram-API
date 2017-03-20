<?php

namespace InstagramAPI;

class LocationResponse extends Response
{
    /**
     * @var Location[]
     */
    public $venues;
    /**
     * @var string
     */
    public $request_id;
}
