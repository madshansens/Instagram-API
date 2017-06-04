<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class DiscoverTopLiveResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $auto_load_more_enabled;
    /**
     * @var Model\BroadcastItem[]
     */
    public $broadcasts;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
}
