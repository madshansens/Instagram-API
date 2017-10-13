<?php

namespace InstagramAPI\Realtime;

use Evenement\EventEmitterInterface;
use InstagramAPI\AutoPropertyMapper;
use Psr\Log\LoggerInterface;

/**
 * Action.
 *
 * @method mixed getAction()
 * @method mixed getStatus()
 * @method bool isAction()
 * @method bool isStatus()
 * @method $this setAction(mixed $value)
 * @method $this setStatus(mixed $value)
 * @method $this unsetAction()
 * @method $this unsetStatus()
 */
abstract class Action extends AutoPropertyMapper
{
    const ACK = 'item_ack';
    const UNKNOWN = 'unknown';

    const JSON_PROPERTY_MAP = [
        'status' => '',
        'action' => '',
    ];

    /**
     * Action handler.
     *
     * @param EventEmitterInterface $target
     * @param LoggerInterface       $logger
     */
    abstract public function handle(
        EventEmitterInterface $target,
        LoggerInterface $logger);
}
