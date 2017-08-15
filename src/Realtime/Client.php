<?php

namespace InstagramAPI\Realtime;

use InstagramAPI\Client as HttpClient;
use InstagramAPI\Instagram;
use InstagramAPI\Realtime;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;

abstract class Client
{
    const CONNECTION_TIMEOUT = 5;

    const KEEPALIVE_INTERVAL = 30;

    /** @var float Minimum reconnection interval (in sec) */
    const MIN_RECONNECT_INTERVAL = 0.5;
    /** @var float Maximum reconnection interval (in sec) */
    const MAX_RECONNECT_INTERVAL = 300; // 5 minutes

    /** @var string */
    protected $_id;

    /** @var Instagram */
    protected $_instagram;

    /** @var Realtime */
    protected $_rtc;

    /** @var LoopInterface */
    protected $_loop;

    /** @var TimerInterface */
    protected $_keepaliveTimer;

    /** @var float */
    protected $_keepaliveTimerInterval;

    /** @var TimerInterface */
    protected $_reconnectTimer;

    /** @var float */
    protected $_reconnectTimerInterval;

    /** @var bool */
    protected $_shutdown;

    /** @var bool */
    protected $_isConnected;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var \JsonMapper */
    protected $_mapper;

    /**
     * Handle client-specific params.
     *
     * @param array $params
     *
     * @return mixed
     */
    abstract protected function _handleParams(
        array $params);

    /**
     * Constructor.
     *
     * @param string          $id
     * @param Realtime        $rtc
     * @param Instagram       $instagram
     * @param LoopInterface   $loop
     * @param LoggerInterface $logger
     * @param array           $params
     */
    public function __construct(
        $id,
        Realtime $rtc,
        Instagram $instagram,
        LoopInterface $loop,
        LoggerInterface $logger,
        array $params = [])
    {
        $this->_id = $id;
        $this->_rtc = $rtc;
        $this->_instagram = $instagram;
        $this->_loop = $loop;
        $this->_logger = $logger;
        $this->_handleParams($params);

        // Create our JSON object mapper and set global default options.
        $this->_mapper = new \JsonMapper();
        $this->_mapper->bStrictNullTypes = false; // Allow NULL values.

        $this->_shutdown = false;
        $this->_isConnected = false;
    }

    /**
     * Return stored Instagram object.
     *
     * @return Instagram
     */
    public function getInstagram()
    {
        return $this->_instagram;
    }

    /**
     * Return stored Instagram client.
     *
     * @return Realtime
     */
    public function getRtc()
    {
        return $this->_rtc;
    }

    /**
     * Return stored logger instance.
     *
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Return client's identifier.
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param object $data
     * @param object $reference
     *
     * @throws \JsonMapper_Exception
     *
     * @return object
     */
    public function mapToJson(
        $data,
        $reference)
    {
        // Use API developer debugging? Throws if class lacks properties.
        $this->_mapper->bExceptionOnUndefinedProperty = $this->_instagram->apiDeveloperDebug;

        // Perform mapping of all object properties.
        $result = $this->_mapper->map($data, $reference);

        return $result;
    }

    /**
     * onKeepaliveTimer event.
     */
    public function onKeepaliveTimer()
    {
        $this->_disconnect();
    }

    /**
     * Emit onKeepaliveTimer event.
     */
    public function emitKeepaliveTimer()
    {
        $this->_keepaliveTimer = null;
        $this->_logger->warning('Keepalive timer is fired.');
        $this->onKeepaliveTimer();
    }

    /**
     * Cancel keepalive timer.
     */
    public function cancelKeepaliveTimer()
    {
        if ($this->_keepaliveTimer === null) {
            return;
        }
        // Cancel existing timer.
        if ($this->_keepaliveTimer->isActive()) {
            $this->_logger->info('Existing keepalive timer has been cancelled.');
            $this->_keepaliveTimer->cancel();
        }
        $this->_keepaliveTimer = null;
    }

    /**
     * Cancel reconnect timer.
     */
    public function cancelReconnectTimer()
    {
        if ($this->_reconnectTimer === null) {
            return;
        }
        // Cancel existing timer.
        if ($this->_reconnectTimer->isActive()) {
            $this->_logger->info('Existing reconnect timer has been cancelled');
            $this->_reconnectTimer->cancel();
        }
        $this->_reconnectTimer = null;
    }

    /**
     * Update keepalive interval (if needed) and set keepalive timer.
     *
     * @param float|null $interval
     */
    public function setKeepaliveTimer(
        $interval = null)
    {
        // Cancel existing timer to prevent double-firing.
        $this->cancelKeepaliveTimer();
        // Do not keepalive on shutdown.
        if ($this->_shutdown) {
            return;
        }
        // Do not set timer if we don't have interval yet.
        if ($interval === null && $this->_keepaliveTimerInterval === null) {
            return;
        }
        // Update interval if new value was supplied.
        if ($interval !== null) {
            $this->_keepaliveTimerInterval = max(0, $interval);
        }
        // Set up new timer.
        $this->_logger->info(sprintf('Setting up keepalive timer to %.1f seconds', $this->_keepaliveTimerInterval));
        $this->_keepaliveTimer = $this->_loop->addTimer($this->_keepaliveTimerInterval, function () {
            $this->emitKeepaliveTimer();
        });
    }

    /**
     * Establish connection.
     */
    abstract protected function _connect();

    /**
     * Update reconnect interval and set up reconnect timer.
     *
     * @param float $interval
     */
    public function setReconnectTimer(
        $interval)
    {
        // Cancel existing timers to prevent double-firing.
        $this->cancelKeepaliveTimer();
        $this->cancelReconnectTimer();
        // Do not reconnect on shutdown.
        if ($this->_shutdown) {
            return;
        }
        // We must keep interval sane.
        $this->_reconnectTimerInterval = max(0.1, min($interval, self::MAX_RECONNECT_INTERVAL));
        $this->_logger->info(sprintf('Setting up connection timer to %.1f seconds', $this->_reconnectTimerInterval));
        // Set up new timer.
        $this->_reconnectTimer = $this->_loop->addTimer($this->_reconnectTimerInterval, function () {
            $this->_keepaliveTimerInterval = self::KEEPALIVE_INTERVAL;
            $this->_connect();
        });
    }

    /**
     * Perform first connection in a row.
     */
    final public function connect()
    {
        $this->setReconnectTimer(self::MIN_RECONNECT_INTERVAL);
    }

    /**
     * Perform reconnection after previous failed attempt.
     */
    final public function reconnect()
    {
        // Implement progressive delay.
        $this->setReconnectTimer($this->_reconnectTimerInterval * 2);
    }

    /**
     * Disconnect from server.
     */
    abstract protected function _disconnect();

    /**
     * Proxy for _disconnect().
     */
    final public function shutdown()
    {
        if (!$this->_isConnected) {
            return;
        }
        $this->_logger->info('Shutting down');
        $this->_shutdown = true;
        $this->_disconnect();
    }

    /**
     * Check if feature is enabled.
     *
     * @param array  $params
     * @param string $feature
     *
     * @return bool
     */
    public static function isFeatureEnabled(
        array $params,
        $feature)
    {
        if (!isset($params[$feature])) {
            return false;
        }

        return in_array($params[$feature], ['enabled', 'true', '1']);
    }

    /**
     * Send command.
     *
     * @param string $command
     *
     * @return bool
     */
    abstract protected function _sendCommand(
        $command);

    /**
     * Checks whether we can send something.
     *
     * @return bool
     */
    public function isSendingAvailable()
    {
        return $this->_isConnected;
    }

    /**
     * Proxy for _sendCommand().
     *
     * @param string $command
     *
     * @return bool
     */
    public function sendCommand(
        $command)
    {
        if (!$this->isSendingAvailable()) {
            return false;
        }

        try {
            return $this->_sendCommand($command);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Handle incoming action.
     *
     * @param Action $action
     */
    protected function _handleAction(
        Action $action)
    {
        $action->handle($this);
    }

    /**
     * Process incoming action.
     *
     * @param object $message
     */
    protected function _processAction(
        $message)
    {
        $this->_logger->info(sprintf('Received action "%s"', $message->action));
        switch ($message->action) {
            case Action::ACK:
                /** @var Action\Ack $action */
                $action = $this->mapToJson($message, new Action\Ack());
                break;
            case Action::UNSEEN_COUNT:
                /** @var Action\Unseen $action */
                $action = $this->mapToJson($message, new Action\Unseen());
                break;
            default:
                $this->_logger->warning(sprintf('Action "%s" is ignored (unknown type)', $message->action));

                return;
        }
        $this->_handleAction($action);
    }

    /**
     * Handle incoming event.
     *
     * @param Event $event
     */
    protected function _handleEvent(
        Event $event)
    {
        $event->handle($this);
    }

    /**
     * Process incoming event.
     *
     * @param object $message
     */
    protected function _processEvent(
        $message)
    {
        $this->_logger->info(sprintf('Received event "%s"', $message->event));
        switch ($message->event) {
            case Event::PATCH:
                /** @var Event\Patch $event */
                $event = $this->mapToJson($message, new Event\Patch());
                break;
            default:
                $this->_logger->warning(sprintf('Event "%s" is ignored (unknown type)', $message->event));

                return;
        }
        $this->_handleEvent($event);
    }

    /**
     * Process single incoming message.
     *
     * @param mixed $message
     */
    protected function _processSingleMessage(
        $message)
    {
        if (isset($message->event)) {
            $this->_processEvent($message);
        } elseif (isset($message->action)) {
            $this->_processAction($message);
        } else {
            $this->_logger->warning('Invalid message (both event and action are missing)');
        }
    }

    /**
     * Process incoming message.
     *
     * @param string $message
     */
    protected function _processMessage(
        $message)
    {
        $this->_logger->info(sprintf('Received message %s', $message));
        $message = HttpClient::api_body_decode($message);
        if (!is_array($message)) {
            $this->_processSingleMessage($message);
        } else {
            foreach ($message as $singleMessage) {
                $this->_processSingleMessage($singleMessage);
            }
        }
    }
}
