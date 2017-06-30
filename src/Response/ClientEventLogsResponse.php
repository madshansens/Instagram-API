<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class ClientEventLogsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $checksum;
    public $config;
    public $app_data;
    public $error;
}
