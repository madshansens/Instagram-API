<?php

namespace InstagramAPI;

class GeoMediaResponse extends Response
{
    protected $geo_media;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->geo_media = [];
            foreach ($response['geo_media'] as $geoMedia) {
                $this->geo_media[] = new GeoMedia($geoMedia);
            }
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getGeoMedia()
    {
        return $this->geo_media;
    }
}
