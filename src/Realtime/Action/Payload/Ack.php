<?php

namespace InstagramAPI\Realtime\Action\Payload;

use InstagramAPI\AutoPropertyHandler;

class Ack extends AutoPropertyHandler
{
    public $activity_status;
    public $client_context;
    public $indicate_activity_ts;
    public $ttl;
}
