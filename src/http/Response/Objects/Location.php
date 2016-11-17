<?php

namespace InstagramAPI;

class Location extends Response
{
    public $name;
    public $external_id_source = null;
    public $external_source = null;
    public $address;
    public $lat;
    public $lng;
    public $external_id = null;
    public $facebook_places_id = null;
    public $city = null;
    public $pk;
}
