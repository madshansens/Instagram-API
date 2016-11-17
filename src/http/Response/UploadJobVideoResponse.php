<?php

namespace InstagramAPI;

class UploadJobVideoResponse extends Response
{
    public $upload_id;
    public $video_upload_urls;

    public function getVideoUploadUrl()
    {
        return $this->getVideoUploadUrls()[3]['url'];
    }

    public function getVideoUploadJob()
    {
        return $this->getVideoUploadUrls()[3]['job'];
    }
}
