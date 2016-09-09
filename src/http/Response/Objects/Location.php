<?php

namespace InstagramAPI;

class Location
{
    protected $name;
    protected $external_id_source;
    protected $address;
    protected $lat;
    protected $lng;
    protected $external_id;

    public function __construct($location)
    {
        $this->name = $location['name'];
        $this->external_id_source = $location['external_id_source'];
        if ((isset($location['address'])) && (!empty($location['address']))) {
            $this->address = $location['address'];
        }
        $this->lat = $location['lat'];
        $this->lng = $location['lng'];
        $this->external_id = $location['external_id'];
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExternalIdSource()
    {
        return $this->external_id_source;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function getLatitude()
    {
        return $this->lat;
    }

    public function getLongitude()
    {
        return $this->lng;
    }

    public function getExternalId()
    {
        return $this->external_id;
    }
}
