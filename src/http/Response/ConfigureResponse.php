<?php

namespace InstagramAPI;

class ConfigureResponse extends Response
{
    public $upload_id;
    public $media_id;
    public $image_url;
    public $media_code;

    public function getMediaUrl()
    {
        return 'https://www.instagram.com/p/'.$this->getMediaCode().'/';
    }
}
