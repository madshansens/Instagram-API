<?php

namespace InstagramAPI\Realtime\Action;

use InstagramAPI\Realtime\Client;

class Ack extends \InstagramAPI\Realtime\Action
{
    public $status_code;
    /**
     * @var \InstagramAPI\Realtime\Action\Payload\Ack
     */
    public $payload;

    /** {@inheritdoc} */
    public function handle(
        Client $client)
    {
        $client->getRtc()->emit('client-context-ack', [$this]);
    }
}
