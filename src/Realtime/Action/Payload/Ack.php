<?php

namespace InstagramAPI\Realtime\Action\Payload;

class Ack extends \InstagramAPI\AutoPropertyHandler
{
    public $activity_status;
    public $client_context;
    public $indicate_activity_ts;
    public $ttl;
}
