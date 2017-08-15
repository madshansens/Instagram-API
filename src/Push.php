<?php

namespace InstagramAPI;

use Evenement\EventEmitterInterface;
use Evenement\EventEmitterTrait;
use Fbns\Client\Auth\DeviceAuth;
use Fbns\Client\Connection;
use Fbns\Client\Lite;
use Fbns\Client\Message\Push as PushMessage;
use Fbns\Client\Message\Register;
use InstagramAPI\Push\Notification;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Socket\ConnectorInterface;

/**
 * The following events are emitted:
 *  - incoming - New PUSH notification has been received.
 *  - like - Someone has liked your media.
 *  - comment - Someone has commented your media.
 *  - direct_v2_message - Someone has messaged you.
 *  - ...
 *  - error - An event of severity "error" occurred.
 */
class Push implements EventEmitterInterface
{
    use EventEmitterTrait;

    const CONNECTION_TIMEOUT = 5;

    /** @var int Minimum reconnection interval (in sec) */
    const MIN_RECONNECT_INTERVAL = 1;
    /** @var int Maximum reconnection interval (in sec) */
    const MAX_RECONNECT_INTERVAL = 300; // 5 minutes

    const DEFAULT_HOST = 'mqtt-mini.facebook.com';
    const DEFAULT_PORT = 443;

    /** @var LoopInterface */
    protected $_loop;

    /** @var Instagram */
    protected $_instagram;

    /** @var Lite */
    protected $_client;

    /** @var Connection */
    protected $_connection;

    /** @var int */
    protected $_reconnectInterval;

    /** @var TimerInterface */
    protected $_reconnectTimer;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var bool */
    protected $_isActive;

    /**
     * @param string $host
     *
     * @return ConnectorInterface
     */
    protected function _getConnector(
        $host)
    {
        return Utils::getSecureConnector(
            $this->_loop,
            Utils::getSecureContext($this->_instagram->getVerifySSL()),
            Utils::getProxyForHost($host, $this->_instagram->getProxy())
        );
    }

    /**
     * Cancel a reconnect timer (if any).
     */
    protected function _cancelReconnectTimer()
    {
        if ($this->_reconnectTimer !== null) {
            if ($this->_reconnectTimer->isActive()) {
                $this->_logger->info('Existing reconnect timer has been canceled.');
                $this->_reconnectTimer->cancel();
            }
            $this->_reconnectTimer = null;
        }
    }

    /**
     * Set up a new reconnect timer with exponential backoff.
     */
    protected function _setReconnectTimer()
    {
        $this->_cancelReconnectTimer();
        if (!$this->_isActive) {
            return;
        }
        $this->_reconnectInterval = min(
            self::MAX_RECONNECT_INTERVAL,
            max(
                self::MIN_RECONNECT_INTERVAL,
                $this->_reconnectInterval * 2
            )
        );
        $this->_logger->info(sprintf('Setting up reconnect timer to %d seconds.', $this->_reconnectInterval));
        $this->_reconnectTimer = $this->_loop->addTimer($this->_reconnectInterval, function () {
            $this->_client->connect(Push::DEFAULT_HOST, Push::DEFAULT_PORT, $this->_connection, Push::CONNECTION_TIMEOUT)
                ->otherwise(function () {
                    $this->_setReconnectTimer();
                });
        });
    }

    /**
     * Create a new FBNS client instance.
     *
     * @return Lite
     */
    protected function _getClient()
    {
        $client = new Lite($this->_loop, $this->_getConnector(self::DEFAULT_HOST), $this->_logger);

        // Bind events.
        $client
            ->on('connect', function (Lite\ConnectResponsePacket $responsePacket) {
                // Reset reconnect interval on successful connection attempt.
                $this->_reconnectInterval = 0;

                // Update auth credentials.
                $authJson = $responsePacket->getAuth();
                if (strlen($authJson)) {
                    $this->_logger->info('Received a non-empty auth.', [$authJson]);

                    try {
                        /** @var DeviceAuth $auth */
                        $auth = $this->_connection->getAuth();
                        $auth->read($authJson);
                        $this->_instagram->settings->setFbnsAuth($auth);
                    } catch (\Exception $e) {
                        $this->_logger->error(sprintf('Failed to update FBNS auth: %s', $e->getMessage()), [$authJson]);
                    }
                }

                // Register an application.
                $this->_client->register(Constants::PACKAGE_NAME, Constants::FACEBOOK_ANALYTICS_APPLICATION_ID);
            })
            ->on('disconnect', function () {
                // Try to reconnect.
                $this->_setReconnectTimer();
            })
            ->on('register', function (Register $message) {
                if (!empty($message->getError())) {
                    $this->emit('error', [new \RuntimeException($message->getError())]);

                    return;
                }
                // Register the received token.
                try {
                    $this->_instagram->push->register('mqtt', $message->getToken());
                } catch (\Exception $e) {
                    $this->emit('error', $e);
                }
            })
            ->on('push', function (PushMessage $message) {
                $payload = $message->getPayload();

                try {
                    $notification = new Notification($payload);
                } catch (\Exception $e) {
                    $this->_logger->error(sprintf('Failed to decode push: %s', $e->getMessage()), [$payload]);

                    return;
                }
                $collapseKey = $notification->getCollapseKey();
                $this->_logger->info(sprintf('Received a push with collapse key "%s"', $collapseKey), [$payload]);
                $this->emit('incoming', [$notification]);
                if (!empty($collapseKey)) {
                    $this->emit($collapseKey, [$notification]);
                }
            });

        return $client;
    }

    /**
     * Init connection params.
     *
     * @return Connection
     */
    protected function _getConnection()
    {
        return new Connection($this->_instagram->settings->getFbnsAuth(), $this->_instagram->device->getFbUserAgent());
    }

    /**
     * Push constructor.
     *
     * @param LoopInterface        $loop
     * @param Instagram            $instagram
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        LoopInterface $loop,
        Instagram $instagram,
        LoggerInterface $logger = null)
    {
        // Save arguments.
        $this->_loop = $loop;
        $this->_instagram = $instagram;
        if ($logger === null) {
            $logger = new NullLogger();
        }
        $this->_logger = $logger;

        // Init connection params and create a client.
        $this->_connection = $this->_getConnection();
        $this->_client = $this->_getClient();

        $this->_isActive = false;
    }

    /**
     * Start Push receiver.
     */
    public function start()
    {
        $this->_logger->info('Starting FBNS client...');
        $this->_isActive = true;
        $this->_reconnectInterval = 0;
        $this->_setReconnectTimer();
    }

    /**
     * Stop Push receiver.
     */
    public function stop()
    {
        $this->_logger->info('Stopping FBNS client...');
        $this->_isActive = false;
        $this->_client->disconnect();
    }
}
