<?php

namespace InstagramAPI\Realtime\Event;

use Evenement\EventEmitterInterface;

/**
 * @method \InstagramAPI\Realtime\Event\Patch\Op[] getData()
 * @method bool getLazy()
 * @method int getMessageType()
 * @method int getNumEndpoints()
 * @method int getSeqId()
 * @method bool isData()
 * @method bool isLazy()
 * @method bool isMessageType()
 * @method bool isNumEndpoints()
 * @method bool isSeqId()
 * @method setData(\InstagramAPI\Realtime\Event\Patch\Op[] $value)
 * @method setLazy(bool $value)
 * @method setMessageType(int $value)
 * @method setNumEndpoints(int $value)
 * @method setSeqId(int $value)
 */
class Patch extends \InstagramAPI\Realtime\Event
{
    /** @var \InstagramAPI\Realtime\Event\Patch\Op[] */
    public $data;
    /** @var int */
    public $message_type;
    /** @var int */
    public $seq_id;
    /** @var bool */
    public $lazy;
    /** @var int */
    public $num_endpoints;

    /** {@inheritdoc} */
    public function handle(
        EventEmitterInterface $target)
    {
        foreach ($this->data as $op) {
            $op->handle($target, $this->_jsonMapper, $this->_logger);
        }
    }
}
