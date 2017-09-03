<?php

namespace InstagramAPI\Realtime;

use Evenement\EventEmitterInterface;
use InstagramAPI\AutoPropertyHandler;
use JsonMapper;
use Psr\Log\LoggerInterface;

/**
 * @method mixed getEvent()
 * @method bool isEvent()
 * @method setEvent(mixed $value)
 */
abstract class Event extends AutoPropertyHandler
{
    const SUBSCRIBED = 'subscribed';
    const UNSUBSCRIBED = 'unsubscribed';
    const KEEPALIVE = 'keepalive';
    const PATCH = 'patch';
    const BROADCAST_ACK = 'broadcast-ack';
    const ERROR = 'error';

    /** @var JsonMapper */
    protected $_jsonMapper;

    /** @var LoggerInterface */
    protected $_logger;

    public $event;

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
