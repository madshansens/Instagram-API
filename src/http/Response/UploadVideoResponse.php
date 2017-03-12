<?php

namespace InstagramAPI;

class UploadVideoResponse extends Response
{
    /**
     * @var string
     */
    public $upload_id;
    /**
     * @var string
     */
    public $configure_delay_ms;
    /**
     * @var string
     */
    public $result;
    public $message;
}
