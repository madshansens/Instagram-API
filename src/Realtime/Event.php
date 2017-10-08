<?php

namespace InstagramAPI\Realtime;

use Evenement\EventEmitterInterface;
use InstagramAPI\AutoPropertyMapper;
use JsonMapper;
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

    /** @var JsonMapper */
    protected $_jsonMapper;

    /** @var LoggerInterface */
    protected $_logger;

    /**
     * Constructor.
     *
     * @param JsonMapper      $jsonMapper
     * @param LoggerInterface $logger
     */
    public function __construct(
        JsonMapper $jsonMapper,
        LoggerInterface $logger)
    {
        $this->_jsonMapper = $jsonMapper;
        $this->_logger = $logger;
    }

    /**
     * Event handler.
     *
     * @param EventEmitterInterface $target
     */
    abstract public function handle(
        EventEmitterInterface $target);
}
