<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class ReportExploreMediaResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $explore_report_status;
}
