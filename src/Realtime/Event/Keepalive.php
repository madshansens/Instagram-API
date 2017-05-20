<?php

namespace InstagramAPI\Realtime\Event;

use InstagramAPI\Realtime\Client;

class Keepalive extends \InstagramAPI\Realtime\Event
{
    public $interval;

    /** {@inheritdoc} */
    public function handle(
        Client $client)
    {
        $client->setKeepaliveTimer($this->interval);
    }
}
