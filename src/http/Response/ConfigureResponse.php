<?php

namespace InstagramAPI;

class ConfigureResponse extends Response
{
    var $upload_id;
    var $media_id;
    var $image_url;
    var $media_code;
    
    public function getMediaUrl()
    {
        return 'https://www.instagram.com/p/'.$this->getMediaCode().'/';
    }
}
