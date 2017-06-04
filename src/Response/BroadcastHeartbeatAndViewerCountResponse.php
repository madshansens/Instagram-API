<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class BroadcastHeartbeatAndViewerCountResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $broadcast_status;
    public $viewer_count;
}
