<?php

namespace InstagramAPI;

class GeoMedia extends Response
{
    /**
     * @var string
     */
    public $media_id;
    public $display_url;
    public $low_res_url;
    public $lat;
    public $lng;
    public $thumbnail;
}
