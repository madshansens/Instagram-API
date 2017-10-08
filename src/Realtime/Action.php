<?php

namespace InstagramAPI\Realtime;

use Evenement\EventEmitterInterface;
use InstagramAPI\AutoPropertyMapper;

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
     */
    abstract public function handle(
        EventEmitterInterface $target);
}
