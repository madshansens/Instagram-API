<?php

namespace InstagramAPI\Realtime;

abstract class Event extends \InstagramAPI\AutoPropertyHandler
{
    const SUBSCRIBED = 'subscribed';
    const UNSUBSCRIBED = 'unsubscribed';
    const KEEPALIVE = 'keepalive';
    const PATCH = 'patch';
    const BROADCAST_ACK = 'broadcast-ack';
    const ERROR = 'error';

    public $event;

    /**
     * Event handler.
     *
     * @param Client $client
     */
    abstract public function handle(
        Client $client);
}
