<?php

namespace InstagramAPI;

class UploadVideoResponse extends Response
{
    /**
     * @var string
     */
    public $upload_id;
    /**
     * @var float
     */
    public $configure_delay_ms;
    public $result;
    public $message;
}
