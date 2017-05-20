<?php

namespace InstagramAPI\Realtime\Event;

use InstagramAPI\Realtime\Client;

class Subscribed extends \InstagramAPI\Realtime\Event
{
    public $sequence;
    public $must_refresh;
    public $topic;

    /** {@inheritdoc} */
    public function handle(
        Client $client)
    {
        $client->onSubscribedTo($this->topic);
        $client->onUpdateSequence($this->topic, $this->sequence);
        if ($this->must_refresh) {
            $client->onRefreshRequested();
        }
    }
}
