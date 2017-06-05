<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class FetchQPDataResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $qp_data;
    public $request_status;
    public $extra_info;
}
