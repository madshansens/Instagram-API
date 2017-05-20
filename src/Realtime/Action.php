<?php

namespace InstagramAPI\Realtime;

abstract class Action extends \InstagramAPI\AutoPropertyHandler
{
    const ACK = 'item_ack';
    const UNSEEN_COUNT = 'inbox_unseen_count';
    const UNKNOWN = 'unknown';

    public $status;
    public $action;

    /**
     * Action handler.
     *
     * @param Client $client
     */
    abstract public function handle(
        Client $client);
}
