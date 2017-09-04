<?php

namespace InstagramAPI\Realtime;

use BinSoul\Net\Mqtt\Client\React\ReactMqttClient;
use BinSoul\Net\Mqtt\DefaultConnection;
use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Message;
use Evenement\EventEmitterInterface;
use Fbns\Client\AuthInterface;
use InstagramAPI\Client as HttpClient;
use InstagramAPI\Constants;
use InstagramAPI\Devices\Device;
use InstagramAPI\React\PersistentInterface;
use InstagramAPI\React\PersistentTrait;
use InstagramAPI\Realtime;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Timer\TimerInterface;
use React\Socket\ConnectorInterface;

class Mqtt implements PersistentInterface
{
    use PersistentTrait;

    const CONNECTION_TIMEOUT = 5;

    const REALTIME_CLIENT_TYPE = 'mqtt';

    /* PubSub topics */
    const DIRECT_TOPIC_TEMPLATE = 'ig/u/v1/%s';
    const LIVE_TOPIC_TEMPLATE = 'ig/live_notification_subscribe/%s';

    /* GraphQL subscription topics */
    const TYPING_TOPIC_TEMPLATE = '1/graphqlsubscriptions/17867973967082385/{"input_data": {"user_id":%s}}';

    /* MQTT server options */
    const DEFAULT_HOST = 'edge-mqtt.facebook.com';
    const DEFAULT_PORT = 443;

    /** MQTT protocol options */
    const MQTT_KEEPALIVE = 900;
    const MQTT_VERSION = 3;

    /** MQTT QoS */
    const FIRE_AND_FORGET = 0;
    const ACKNOWLEDGED_DELIVERY = 1;

    /** Topic+ID pairs */
    const PUBSUB_TOPIC = '/pubsub';
    const PUBSUB_TOPIC_ID = '88';

    const SEND_MESSAGE_TOPIC = '/ig_send_message';
    const SEND_MESSAGE_TOPIC_ID = '132';

    const SEND_MESSAGE_RESPONSE_TOPIC = '/ig_send_message_response';
    const SEND_MESSAGE_RESPONSE_TOPIC_ID = '133';

    const IRIS_SUB_TOPIC = '/ig_sub_iris';
    const IRIS_SUB_TOPIC_ID = '134';

    const IRIS_SUB_RESPONSE_TOPIC = '/ig_sub_iris_response';
    const IRIS_SUB_RESPONSE_TOPIC_ID = '135';

    const MESSAGE_SYNC_TOPIC = '/ig_message_sync';
    const MESSAGE_SYNC_TOPIC_ID = '146';

    const REALTIME_SUB_TOPIC = '/ig_realtime_sub';
    const REALTIME_SUB_TOPIC_ID = '149';

    const GRAPHQL_TOPIC = '/graphql';
    const GRAPHQL_TOPIC_ID = '9';

    /** MQTT client options */
    const NETWORK_TYPE_WIFI = 1;
    const CLIENT_TYPE = 'cookie_auth';
    const PUBLISH_FORMAT = 'jz';

    /** MQTT client capabilities */
    const CP_ACKNOWLEDGED_DELIVERY = 0;
    const CP_PROCESSING_LASTACTIVE_PRESENCEINFO = 1;
    const CP_EXACT_KEEPALIVE = 2;
    const CP_REQUIRES_JSON_UNICODE_ESCAPES = 3;
    const CP_DELTA_SENT_MESSAGE_ENABLED = 4;
    const CP_USE_ENUM_TOPIC = 5;
    const CP_SUPPRESS_GETDIFF_IN_CONNECT = 6;
    const CP_USE_THRIFT_FOR_INBOX = 7;
    const CP_USE_SEND_PINGRESP = 8;
    const CP_REQUIRE_REPLAY_PROTECTION = 9;
    const CP_DATA_SAVING_MODE = 10;
    const CP_TYPING_OFF_WHEN_SENDING_MESSAGE = 11;

    const INVALID_SEQUENCE_ID = -1;

    /** @var EventEmitterInterface */
    protected $_target;

    /** @var ConnectorInterface */
    protected $_connector;

    /** @var AuthInterface */
    protected $_auth;

    /** @var Device */
    protected $_device;

    /** @var LoopInterface */
    protected $_loop;

    /** @var TimerInterface */
    protected $_keepaliveTimer;

    /** @var bool */
    protected $_shutdown;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var \JsonMapper */
    protected $_mapper;

    /** @var int */
    protected $_capabilities;

    /** @var string[] */
    protected $_pubsubTopics;
    /** @var string[] */
    protected $_graphqlTopics;

    /** @var ReactMqttClient */
    protected $_client;

    /** @var bool */
    protected $_mqttLiveEnabled;
    /** @var bool */
    protected $_irisEnabled;
    /** @var string|null */
    protected $_msgTypeBlacklist;
    /** @var bool */
    protected $_graphQlEnabled;
    /** @var int */
    protected $_sequenceId;

    /**
     * Constructor.
     *
     * @param EventEmitterInterface $target
     * @param ConnectorInterface    $connector
     * @param AuthInterface         $auth
     * @param Device                $device
     * @param array                 $experiments
     * @param LoopInterface         $loop
     * @param LoggerInterface       $logger
     */
    public function __construct(
        EventEmitterInterface $target,
        ConnectorInterface $connector,
        AuthInterface $auth,
        Device $device,
        array $experiments,
        LoopInterface $loop,
        LoggerInterface $logger)
    {
        $this->_target = $target;
        $this->_connector = $connector;
        $this->_auth = $auth;
        $this->_device = $device;
        $this->_loop = $loop;
        $this->_logger = $logger;

        // Create our JSON object mapper and set global default options.
        $this->_mapper = new \JsonMapper();
        $this->_mapper->bStrictNullTypes = false; // Allow NULL values.

        $this->_loadExperiments($experiments);

        $this->_shutdown = false;
        $this->_client = $this->_getClient();
    }

    /** {@inheritdoc} */
    public function getLoop()
    {
        return $this->_loop;
    }

    /** {@inheritdoc} */
    public function isActive()
    {
        return !$this->_shutdown;
    }

    /**
     * Cancel a keepalive timer (if any).
     */
    protected function _cancelKeepaliveTimer()
    {
        if ($this->_keepaliveTimer !== null) {
            if ($this->_keepaliveTimer->isActive()) {
                $this->_logger->info('Existing keepalive timer has been canceled.');
                $this->_keepaliveTimer->cancel();
            }
            $this->_keepaliveTimer = null;
        }
    }

    /**
     * Set up a new keepalive timer.
     */
    protected function _setKeepaliveTimer()
    {
        $this->_cancelKeepaliveTimer();
        $keepaliveInterval = self::MQTT_KEEPALIVE;
        $this->_logger->info(sprintf('Setting up keepalive timer to %d seconds', $keepaliveInterval));
        $this->_keepaliveTimer = $this->_loop->addTimer($keepaliveInterval, function () {
            $this->_logger->info('Keepalive timer has been fired.');
            $this->_disconnect();
        });
    }

    /**
     * Try to establish a connection.
     */
    protected function _connect()
    {
        $this->_setReconnectTimer(function () {
            $this->_logger->info(sprintf('Connecting to %s:%d...', self::DEFAULT_HOST, self::DEFAULT_PORT));

            $connection = new DefaultConnection(
                $this->_getMqttUsername(),
                $this->_auth->getPassword(),
                null,
                $this->_auth->getClientId(),
                self::MQTT_KEEPALIVE,
                self::MQTT_VERSION,
                true
            );

            return $this->_client->connect(self::DEFAULT_HOST, self::DEFAULT_PORT, $connection, self::CONNECTION_TIMEOUT);
        });
    }

    /**
     * Perform first connection in a row.
     */
    public function start()
    {
        $this->_shutdown = false;
        $this->_reconnectInterval = 0;
        $this->_connect();
    }

    /**
     * Whether connection is established.
     *
     * @return bool
     */
    protected function _isConnected()
    {
        return $this->_client->isConnected();
    }

    /**
     * Disconnect from server.
     */
    protected function _disconnect()
    {
        $this->_cancelKeepaliveTimer();
        $this->_client->disconnect();
    }

    /**
     * Proxy for _disconnect().
     */
    public function stop()
    {
        $this->_logger->info('Shutting down...');
        $this->_shutdown = true;
        $this->_cancelReconnectTimer();
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
    protected function _isFeatureEnabled(
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
    protected function _sendCommand(
        $command)
    {
        $this->_publish(self::SEND_MESSAGE_TOPIC, $command, self::FIRE_AND_FORGET);

        return true;
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
        if (!$this->_isConnected()) {
            return false;
        }

        try {
            return $this->_sendCommand($command);
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());

            return false;
        }
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
                $action = $this->_mapper->map($message, new Action\Ack());
                break;
            default:
                $this->_logger->warning(sprintf('Action "%s" is ignored (unknown type)', $message->action));

                return;
        }
        $action->handle($this->_target);
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
                $event = $this->_mapper->map($message, new Event\Patch($this->_mapper, $this->_logger));
                break;
            default:
                $this->_logger->warning(sprintf('Event "%s" is ignored (unknown type)', $message->event));

                return;
        }
        $event->handle($this->_target);
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

    /**
     * Load experiments.
     *
     * @param array $experiments
     */
    protected function _loadExperiments(
        array $experiments)
    {
        // Direct features.
        $directFeatures = isset($experiments['ig_android_realtime_iris'])
            ? $experiments['ig_android_realtime_iris'] : [];
        $this->_irisEnabled = $this->_isFeatureEnabled($directFeatures, 'is_direct_over_iris_enabled');
        if (isset($directFeatures['pubsub_msg_type_blacklist'])) {
            $this->_msgTypeBlacklist = $directFeatures['msgTypeBlacklist'];
        }

        // Live features.
        $liveFeatures = isset($experiments['ig_android_skywalker_live_event_start_end'])
            ? $experiments['ig_android_skywalker_live_event_start_end'] : [];
        $this->_mqttLiveEnabled = $this->_isFeatureEnabled($liveFeatures, 'is_enabled');

        // GraphQL features.
        $graphqlFeatures = isset($experiments['ig_android_gqls_typing_indicator'])
            ? $experiments['ig_android_gqls_typing_indicator'] : [];
        $this->_graphQlEnabled = $this->_isFeatureEnabled($graphqlFeatures, 'is_enabled');

        // Set up PubSub topics.
        $this->_pubsubTopics = [];
        if ($this->_mqttLiveEnabled) {
            $this->_pubsubTopics[] = sprintf(self::LIVE_TOPIC_TEMPLATE, $this->_auth->getUserId());
        }
        $this->_pubsubTopics[] = sprintf(self::DIRECT_TOPIC_TEMPLATE, $this->_auth->getUserId());

        // Set up GraphQL topics.
        $this->_graphqlTopics = [];
        if ($this->_graphQlEnabled) {
            $this->_graphqlTopics[] = sprintf(self::TYPING_TOPIC_TEMPLATE, $this->_auth->getUserId());
        }
    }

    /**
     * Return MQTT client capabilities.
     *
     * @return int
     */
    protected function _getCapabilities()
    {
        if ($this->_capabilities === null) {
            $this->_capabilities = 0
                | 1 << self::CP_ACKNOWLEDGED_DELIVERY
                | 1 << self::CP_PROCESSING_LASTACTIVE_PRESENCEINFO
                | 1 << self::CP_EXACT_KEEPALIVE
                | 0 << self::CP_REQUIRES_JSON_UNICODE_ESCAPES
                | 1 << self::CP_DELTA_SENT_MESSAGE_ENABLED
                | 1 << self::CP_USE_ENUM_TOPIC
                | 0 << self::CP_SUPPRESS_GETDIFF_IN_CONNECT
                | 1 << self::CP_USE_THRIFT_FOR_INBOX
                | 1 << self::CP_USE_SEND_PINGRESP
                | 0 << self::CP_REQUIRE_REPLAY_PROTECTION
                | 0 << self::CP_DATA_SAVING_MODE
                | 0 << self::CP_TYPING_OFF_WHEN_SENDING_MESSAGE;
        }

        return $this->_capabilities;
    }

    /**
     * Returns application specific info.
     *
     * @return array
     */
    protected function _getAppSpecificInfo()
    {
        $result = [
            'platform'      => Constants::PLATFORM,
            'app_version'   => Constants::IG_VERSION,
            'capabilities'  => Constants::X_IG_Capabilities,
            'User-Agent'    => $this->_device->getUserAgent(),
            'ig_mqtt_route' => 'django',
        ];
        // PubSub message type blacklist.
        $msgTypeBlacklist = '';
        if ($this->_msgTypeBlacklist !== null && strlen($this->_msgTypeBlacklist)) {
            $msgTypeBlacklist = $this->_msgTypeBlacklist;
        }
        if ($this->_graphQlEnabled) {
            if (strlen($msgTypeBlacklist)) {
                $msgTypeBlacklist .= ', typing_type';
            } else {
                $msgTypeBlacklist = 'typing_type';
            }
        }
        if (strlen($msgTypeBlacklist)) {
            $result['pubsub_msg_type_blacklist'] = $msgTypeBlacklist;
        }
        // Accept-Language should be last one.
        $result['Accept-Language'] = Constants::ACCEPT_LANGUAGE;

        return $result;
    }

    /**
     * Returns username for MQTT connection.
     *
     * @return string
     */
    protected function _getMqttUsername()
    {
        // Session ID is uptime in msec.
        $sessionId = (microtime(true) - strtotime('Last Monday')) * 1000;
        // Random buster-string to avoid clashing with other data.
        $randNum = mt_rand(1000000, 9999999);
        $topics = [
            self::PUBSUB_TOPIC,
        ];
        if ($this->_graphQlEnabled) {
            $topics[] = self::REALTIME_SUB_TOPIC;
        }
        $topics[] = self::SEND_MESSAGE_RESPONSE_TOPIC;
        if ($this->_irisEnabled) {
            $topics[] = self::IRIS_SUB_RESPONSE_TOPIC;
            $topics[] = self::MESSAGE_SYNC_TOPIC;
        }
        $result = json_encode([
            // USER_ID
            'u'                 => '%ACCOUNT_ID_'.$randNum.'%',
            // AGENT
            'a'                 => $this->_device->getFbUserAgent(Constants::INSTAGRAM_APPLICATION_NAME),
            // CAPABILITIES
            'cp'                => $this->_getCapabilities(),
            // CLIENT_MQTT_SESSION_ID
            'mqtt_sid'          => '%SESSION_ID_'.$randNum.'%',
            // NETWORK_TYPE
            'nwt'               => self::NETWORK_TYPE_WIFI,
            // NETWORK_SUBTYPE
            'nwst'              => 0,
            // MAKE_USER_AVAILABLE_IN_FOREGROUND
            'chat_on'           => false,
            // NO_AUTOMATIC_FOREGROUND
            'no_auto_fg'        => true,
            // DEVICE_ID
            'd'                 => $this->_auth->getDeviceId(),
            // DEVICE_SECRET
            'ds'                => $this->_auth->getDeviceSecret(),
            // INITIAL_FOREGROUND_STATE
            'fg'                => false,
            // ENDPOINT_CAPABILITIES
            'ecp'               => 0,
            // PUBLISH_FORMAT
            'pf'                => self::PUBLISH_FORMAT,
            // CLIENT_TYPE
            'ct'                => self::CLIENT_TYPE,
            // APP_ID
            'aid'               => Constants::FACEBOOK_ANALYTICS_APPLICATION_ID,
            // SUBSCRIBE_TOPICS
            'st'                => $topics,
            // CLIENT_STACK
            'clientStack'       => 3,
            // APP_SPECIFIC_INFO
            'app_specific_info' => $this->_getAppSpecificInfo(),
        ]);
        $result = strtr($result, [
            json_encode('%ACCOUNT_ID_'.$randNum.'%') => $this->_auth->getUserId(),
            json_encode('%SESSION_ID_'.$randNum.'%') => round($sessionId),
        ]);

        return $result;
    }

    /**
     * Create a new MQTT client.
     *
     * @return ReactMqttClient
     */
    protected function _getClient()
    {
        $client = new ReactMqttClient($this->_connector, $this->_loop, null, new Mqtt\StreamParser());

        $client->on('error', function (\Exception $e) {
            $this->_logger->error($e->getMessage());
        });
        $client->on('warning', function (\Exception $e) {
            $this->_logger->warning($e->getMessage());
        });
        $client->on('open', function () {
            $this->_logger->info('Connection has been established');
        });
        $client->on('close', function () {
            $this->_logger->info('Connection has been closed');
            $this->_cancelKeepaliveTimer();
            if (!$this->_reconnectInterval) {
                $this->_connect();
            }
        });
        $client->on('connect', function () {
            $this->_logger->info('Connected to a broker');
            $this->_setKeepaliveTimer();
            $this->_subscribe();
        });
        $client->on('ping', function () {
            $this->_logger->info('Ping flow completed');
            $this->_setKeepaliveTimer();
        });
        $client->on('publish', function () {
            $this->_logger->info('Publish flow completed');
            $this->_setKeepaliveTimer();
        });
        $client->on('message', function (Message $message) {
            $this->_setKeepaliveTimer();
            $this->_onReceive($message);
        });
        $client->on('disconnect', function () {
            $this->_logger->info('Disconnected from broker');
        });

        return $client;
    }

    /**
     * Subscribe to Iris.
     */
    protected function _subscribeToIris()
    {
        if (!$this->_irisEnabled || $this->_sequenceId === self::INVALID_SEQUENCE_ID) {
            return;
        }
        $this->_logger->info(sprintf('Subscribing to iris with sequence %d', $this->_sequenceId));
        $command = [
            'seq_id' => $this->_sequenceId,
        ];
        $this->_publish(self::IRIS_SUB_TOPIC, Realtime::jsonEncode($command), self::ACKNOWLEDGED_DELIVERY);
    }

    /**
     * Update Iris sequence ID.
     *
     * @param int $sequenceId
     */
    public function updateSequenceId(
        $sequenceId)
    {
        if ($sequenceId === null || $sequenceId === self::INVALID_SEQUENCE_ID || $this->_sequenceId == $sequenceId) {
            return;
        }
        $this->_sequenceId = $sequenceId;
        $this->_logger->info(sprintf('Sequence updated to %d', $this->_sequenceId));
        if ($this->_isConnected()) {
            $this->_subscribeToIris();
        }
    }

    /**
     * Subscribe to all topics.
     */
    protected function _subscribe()
    {
        if (count($this->_pubsubTopics)) {
            $this->_logger->info(sprintf('Subscribing to pubsub topics %s', implode(', ', $this->_pubsubTopics)));
            $command = [
                'sub' => $this->_pubsubTopics,
            ];
            $this->_publish(self::PUBSUB_TOPIC, Realtime::jsonEncode($command), self::ACKNOWLEDGED_DELIVERY);
        }
        $this->_subscribeToIris();
        if (count($this->_graphqlTopics)) {
            $this->_logger->info(sprintf('Subscribing to graphql topics %s', implode(', ', $this->_graphqlTopics)));
            $command = [
                'sub' => $this->_graphqlTopics,
            ];
            $this->_publish(self::REALTIME_SUB_TOPIC, Realtime::jsonEncode($command), self::ACKNOWLEDGED_DELIVERY);
        }
    }

    /**
     * Unsubscribe from all topics.
     */
    protected function _unsubscribe()
    {
        if (count($this->_pubsubTopics)) {
            $this->_logger->info(sprintf('Unsubscribing from pubsub topics %s', implode(', ', $this->_pubsubTopics)));
            $command = [
                'unsub' => $this->_pubsubTopics,
            ];
            $this->_publish(self::PUBSUB_TOPIC, Realtime::jsonEncode($command), self::ACKNOWLEDGED_DELIVERY);
        }
        if (count($this->_graphqlTopics)) {
            $this->_logger->info(sprintf('Unsubscribing from graphql topics %s', implode(', ', $this->_graphqlTopics)));
            $command = [
                'unsub' => $this->_graphqlTopics,
            ];
            $this->_publish(self::REALTIME_SUB_TOPIC, Realtime::jsonEncode($command), self::ACKNOWLEDGED_DELIVERY);
        }
    }

    /**
     * Returns mapping of human readable topics to their identifiers.
     *
     * @return array
     */
    protected function _getTopicsMapping()
    {
        return [
            self::PUBSUB_TOPIC                => self::PUBSUB_TOPIC_ID,
            self::SEND_MESSAGE_TOPIC          => self::SEND_MESSAGE_TOPIC_ID,
            self::SEND_MESSAGE_RESPONSE_TOPIC => self::SEND_MESSAGE_RESPONSE_TOPIC_ID,
            self::IRIS_SUB_TOPIC              => self::IRIS_SUB_TOPIC_ID,
            self::IRIS_SUB_RESPONSE_TOPIC     => self::IRIS_SUB_RESPONSE_TOPIC_ID,
            self::MESSAGE_SYNC_TOPIC          => self::MESSAGE_SYNC_TOPIC_ID,
            self::REALTIME_SUB_TOPIC          => self::REALTIME_SUB_TOPIC_ID,
            self::GRAPHQL_TOPIC               => self::GRAPHQL_TOPIC_ID,
        ];
    }

    /**
     * Maps human readable topic to its identifier.
     *
     * @param string $topic
     *
     * @return string
     */
    protected function _mapTopic(
        $topic)
    {
        $mapping = $this->_getTopicsMapping();

        return isset($mapping[$topic]) ? $mapping[$topic] : $topic;
    }

    /**
     * @param string $topic
     * @param string $payload
     * @param int    $qosLevel
     */
    protected function _publish(
        $topic,
        $payload,
        $qosLevel)
    {
        $this->_logger->info(sprintf('Sending message %s to topic %s', $payload, $topic));
        $payload = zlib_encode($payload, ZLIB_ENCODING_DEFLATE, 9);
        // We need to map human readable topic name to its ID because of bandwidth saving.
        $topic = $this->_mapTopic($topic);
        $this->_client->publish(new DefaultMessage($topic, $payload, $qosLevel));
    }

    /**
     * Incoming message handler.
     *
     * @param Message $msg
     */
    protected function _onReceive(
        Message $msg)
    {
        $topic = $msg->getTopic();
        $payload = @zlib_decode($msg->getPayload());
        if ($payload === false) {
            $this->_logger->warning('Failed to inflate payload');

            return;
        }
        switch ($topic) {
            case self::PUBSUB_TOPIC:
            case self::PUBSUB_TOPIC_ID:
                $skywalker = new Mqtt\Skywalker($payload);
                if (!in_array($skywalker->getType(), [Mqtt\Skywalker::TYPE_DIRECT, Mqtt\Skywalker::TYPE_LIVE])) {
                    $this->_logger->warning(sprintf('Received Skywalker message with unsupported type %d', $skywalker->getType()));

                    return;
                }
                $payload = $skywalker->getPayload();
                break;
            case self::GRAPHQL_TOPIC:
            case self::GRAPHQL_TOPIC_ID:
            case self::REALTIME_SUB_TOPIC:
            case self::REALTIME_SUB_TOPIC_ID:
                $graphQl = new Mqtt\GraphQl($payload);
                if (!in_array($graphQl->getTopic(), [Mqtt\GraphQl::TOPIC_DIRECT])) {
                    $this->_logger->warning(sprintf('Received GraphQL message with unsupported topic %s', $graphQl->getTopic()));

                    return;
                }
                $payload = $graphQl->getPayload();
                break;
            case self::IRIS_SUB_RESPONSE_TOPIC:
            case self::IRIS_SUB_RESPONSE_TOPIC_ID:
                $json = HttpClient::api_body_decode($payload);
                if (!is_object($json)) {
                    $this->_logger->warning(sprintf('Failed to decode Iris JSON: %s', json_last_error_msg()));

                    return;
                }
                /** @var Mqtt\Iris $iris */
                $iris = $this->_mapper->map($json, new Mqtt\Iris());
                if (!$iris->isSucceeded()) {
                    $this->_logger->warning(sprintf('Failed to subscribe to Iris (%d): %s', $iris->getErrorType(), $iris->getErrorMessage()));
                }

                return;
            case self::SEND_MESSAGE_RESPONSE_TOPIC:
            case self::SEND_MESSAGE_RESPONSE_TOPIC_ID:
            case self::MESSAGE_SYNC_TOPIC:
            case self::MESSAGE_SYNC_TOPIC_ID:
                break;
            default:
                $this->_logger->warning(sprintf('Received message from unsupported topic "%s"', $topic));

                return;
        }
        $this->_processMessage($payload);
    }

    /** {@inheritdoc} */
    public function getLogger()
    {
        return $this->_logger;
    }
}
