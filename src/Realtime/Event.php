<?php

namespace InstagramAPI\Realtime;

use Evenement\EventEmitterInterface;
use InstagramAPI\AutoPropertyMapper;
use Psr\Log\LoggerInterface;

abstract class Event extends AutoPropertyMapper
{
    const SUBSCRIBED = 'subscribed';
    const UNSUBSCRIBED = 'unsubscribed';
    const KEEPALIVE = 'keepalive';
    const PATCH = 'patch';
    const BROADCAST_ACK = 'broadcast-ack';
    const ERROR = 'error';

    const JSON_PROPERTY_MAP = [
        'event' => '',
    ];

    /**
     * Event handler.
     *
     * @param EventEmitterInterface $target
     * @param LoggerInterface       $logger
     */
    abstract public function handle(
        EventEmitterInterface $target,
        LoggerInterface $logger);
}
