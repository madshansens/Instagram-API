<?php

namespace InstagramAPI\Realtime\Event;

use Evenement\EventEmitterInterface;
use InstagramAPI\Realtime\Event as RealtimeEvent;
use Psr\Log\LoggerInterface;

class Patch extends RealtimeEvent
{
    const JSON_PROPERTY_MAP = [
        'data'          => 'Patch\Op[]',
        'message_type'  => 'int',
        'seq_id'        => 'int',
        'lazy'          => 'bool',
        'num_endpoints' => 'int',
    ];

    /** {@inheritdoc} */
    public function handle(
        EventEmitterInterface $target,
        LoggerInterface $logger)
    {
        foreach ($this->_getProperty('data') as $op) {
            $op->handle($target, $logger);
        }
    }
}
