<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class FacebookOTAResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $bundles;
    /**
     * @var string
     */
    public $request_id;
}
