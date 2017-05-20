<?php

namespace InstagramAPI\Realtime\Action;

use InstagramAPI\Realtime\Client;

class Unseen extends \InstagramAPI\Realtime\Action
{
    /**
     * @var \InstagramAPI\Realtime\Action\Payload\Unseen
     */
    public $payload;

    /** {@inheritdoc} */
    public function handle(
        Client $client)
    {
        // We will also receive patch event, so do nothing to prevent double-firing.
        //$client->getRtc()->emit('unseen-count-update', [$this->payload]);
    }
}
