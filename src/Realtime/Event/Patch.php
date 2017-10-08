<?php

namespace InstagramAPI\Realtime\Event;

use Evenement\EventEmitterInterface;
use InstagramAPI\Realtime\Event as RealtimeEvent;

class Patch extends RealtimeEvent
{
    const JSON_PROPERTY_MAP = [
        'data'          => '\InstagramAPI\Realtime\Event\Patch\Op[]',
        'message_type'  => 'int',
        'seq_id'        => 'int',
        'lazy'          => 'bool',
        'num_endpoints' => 'int',
    ];

    /** {@inheritdoc} */
    public function handle(
        EventEmitterInterface $target)
    {
        foreach ($this->data as $op) {
            $op->handle($target, $this->_logger);
        }
    }
}
