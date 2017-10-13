<?php

namespace InstagramAPI\Realtime\Action;

use Evenement\EventEmitterInterface;
use InstagramAPI\Realtime\Action as RealtimeAction;
use Psr\Log\LoggerInterface;

/**
 * Ack.
 *
 * @method mixed getAction()
 * @method \InstagramAPI\Response\Model\DirectSendItemPayload getPayload()
 * @method mixed getStatus()
 * @method mixed getStatusCode()
 * @method bool isAction()
 * @method bool isPayload()
 * @method bool isStatus()
 * @method bool isStatusCode()
 * @method $this setAction(mixed $value)
 * @method $this setPayload(\InstagramAPI\Response\Model\DirectSendItemPayload $value)
 * @method $this setStatus(mixed $value)
 * @method $this setStatusCode(mixed $value)
 * @method $this unsetAction()
 * @method $this unsetPayload()
 * @method $this unsetStatus()
 * @method $this unsetStatusCode()
 */
class Ack extends RealtimeAction
{
    const JSON_PROPERTY_MAP = [
        'status_code' => '',
        'payload'     => '\InstagramAPI\Response\Model\DirectSendItemPayload',
    ];

    /** {@inheritdoc} */
    public function handle(
        EventEmitterInterface $target,
        LoggerInterface $logger)
    {
        $target->emit('client-context-ack', [$this]);
    }
}
