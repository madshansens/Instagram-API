<?php

namespace InstagramAPI\Realtime\Event;

use Evenement\EventEmitterInterface;
use InstagramAPI\Realtime\Event as RealtimeEvent;
use Psr\Log\LoggerInterface;

/**
 * Patch.
 *
 * @method Patch\Op[] getData()
 * @method mixed getEvent()
 * @method bool getLazy()
 * @method int getMessageType()
 * @method int getNumEndpoints()
 * @method int getSeqId()
 * @method bool isData()
 * @method bool isEvent()
 * @method bool isLazy()
 * @method bool isMessageType()
 * @method bool isNumEndpoints()
 * @method bool isSeqId()
 * @method $this setData(Patch\Op[] $value)
 * @method $this setEvent(mixed $value)
 * @method $this setLazy(bool $value)
 * @method $this setMessageType(int $value)
 * @method $this setNumEndpoints(int $value)
 * @method $this setSeqId(int $value)
 * @method $this unsetData()
 * @method $this unsetEvent()
 * @method $this unsetLazy()
 * @method $this unsetMessageType()
 * @method $this unsetNumEndpoints()
 * @method $this unsetSeqId()
 */
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
