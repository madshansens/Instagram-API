<?php

namespace InstagramAPI\Realtime\Action;

use Evenement\EventEmitterInterface;
use InstagramAPI\Realtime\Action as RealtimeAction;

class Ack extends RealtimeAction
{
    const JSON_PROPERTY_MAP = [
        'status_code' => '',
        'payload'     => '\InstagramAPI\Response\Model\DirectSendItemPayload',
    ];

    /** {@inheritdoc} */
    public function handle(
        EventEmitterInterface $target)
    {
        $target->emit('client-context-ack', [$this]);
    }
}
