<?php

namespace InstagramAPI\Realtime;

use Evenement\EventEmitterInterface;
use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getAction()
 * @method mixed getStatus()
 * @method bool isAction()
 * @method bool isStatus()
 * @method setAction(mixed $value)
 * @method setStatus(mixed $value)
 */
abstract class Action extends AutoPropertyHandler
{
    const ACK = 'item_ack';
    const UNKNOWN = 'unknown';

    public $status;
    public $action;

    /**
     * Action handler.
     *
     * @param EventEmitterInterface $target
     */
    abstract public function handle(
        EventEmitterInterface $target);
}
