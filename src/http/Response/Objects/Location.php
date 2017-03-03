<?php

namespace InstagramAPI;

class Location extends Response
{
    public $name;
    /**
     * @var string
     */
    public $external_id_source = null;
    public $external_source = null;
    public $address;
    /**
     * @var float
     */
    public $lat;
    /**
     * @var float
     */
    public $lng;
    /**
     * @var string
     */
    public $external_id = null;
    /**
     * @var string
     */
    public $facebook_places_id = null;
    public $city = null;
    /**
     * @var string
     */
    public $pk;
}
