<?php

namespace InstagramAPI\Realtime\Action;

use InstagramAPI\Realtime\Client;

/**
 * @method \InstagramAPI\Realtime\Action\Payload\Ack getPayload()
 * @method mixed getStatusCode()
 * @method bool isPayload()
 * @method bool isStatusCode()
 * @method setPayload(\InstagramAPI\Realtime\Action\Payload\Ack $value)
 * @method setStatusCode(mixed $value)
 */
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
