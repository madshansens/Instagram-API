<?php

namespace InstagramAPI\Realtime\Event;

use InstagramAPI\Realtime\Client;

class Unsubscribed extends \InstagramAPI\Realtime\Event
{
    public $topic;
    public $must_refresh;

    /** {@inheritdoc} */
    public function handle(
        Client $client)
    {
        $client->onUnsubscribedFrom($this->topic);
    }
}
