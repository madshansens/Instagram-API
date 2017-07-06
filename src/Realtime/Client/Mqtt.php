<?php

namespace InstagramAPI\Realtime\Client;

use BinSoul\Net\Mqtt\Client\React\ReactMqttClient;
use BinSoul\Net\Mqtt\DefaultConnection;
use BinSoul\Net\Mqtt\DefaultMessage;
use BinSoul\Net\Mqtt\Message;
use InstagramAPI\Client as HttpClient;
use InstagramAPI\Constants;
use InstagramAPI\Devices\GoodDevices;
use InstagramAPI\Realtime;
use InstagramAPI\Realtime\Client;
use InstagramAPI\Realtime\Client\Mqtt\Connector;

class Mqtt extends Client
{
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

    /** @var int */
    protected $_capabilities;

    /** @var string[] */
    protected $_pubsubTopics;
    /** @var string[] */
    protected $_graphqlTopics;

    /** @var ReactMqttClient */
    protected $_connection;

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

    /** {@inheritdoc} */
    protected function _handleParams(
        array $params)
    {
        // MQTT params.
        if (isset($params['isMqttLiveEnabled'])) {
            $this->_mqttLiveEnabled = (bool) $params['isMqttLiveEnabled'];
        } else {
            $this->_mqttLiveEnabled = false;
        }
        if (isset($params['isIrisEnabled'])) {
            $this->_irisEnabled = (bool) $params['isIrisEnabled'];
        } else {
            $this->_irisEnabled = false;
        }
        if (isset($params['msgTypeBlacklist'])) {
            $this->_msgTypeBlacklist = $params['msgTypeBlacklist'];
        }
        if (isset($params['isGraphQlEnabled'])) {
            $this->_graphQlEnabled = (bool) $params['isGraphQlEnabled'];
        } else {
            $this->_graphQlEnabled = false;
        }
        if (isset($params['sequenceId'])) {
            $this->_sequenceId = (int) $params['sequenceId'];
        } else {
            $this->_sequenceId = self::INVALID_SEQUENCE_ID;
        }
        // Set up PubSub topics.
        $this->_pubsubTopics = [];
        if ($this->_mqttLiveEnabled) {
            $this->_pubsubTopics[] = sprintf(self::LIVE_TOPIC_TEMPLATE, $this->_instagram->account_id);
        }
        $this->_pubsubTopics[] = sprintf(self::DIRECT_TOPIC_TEMPLATE, $this->_instagram->account_id);
        // Set up GraphQL topics.
        $this->_graphqlTopics = [];
        if ($this->_graphQlEnabled) {
            $this->_graphqlTopics[] = sprintf(self::TYPING_TOPIC_TEMPLATE, $this->_instagram->account_id);
        }
    }

    /** {@inheritdoc} */
    protected function _disconnect()
    {
        if ($this->_connection === null) {
            return;
        }
        $this->_unsubscribe();
        $this->_connection->disconnect();
    }

    /**
     * Escape string for Facebook User-Agent string.
     *
     * @param $string
     *
     * @return string
     */
    protected function _escapeFbString(
        $string)
    {
        $result = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $char = $string[$i];
            if ($char === '&') {
                $result .= '&amp;';
            } elseif ($char < ' ' || $char > '~') {
                $result .= sprintf('&#%d;', ord($char));
            } else {
                $result .= $char;
            }
        }
        $result = strtr($result, ['/' => '-', ';' => '-']);

        return $result;
    }

    /**
     * Generate Facebook User-Agent.
     *
     * @return string
     */
    protected function _getFbUserAgent()
    {
        $device = $this->_instagram->device;
        list($width, $height) = explode('x', $device->getResolution());
        $density = round(str_replace('dpi', '', $device->getDPI()) / 160, 1);
        $result = [
            'FBAN' => Constants::APPLICATION_NAME,
            'FBAV' => Constants::IG_VERSION,
            'FBBV' => Constants::VERSION_CODE,
            'FBDM' => sprintf('{density=%.1f,width=%d,height=%d}', $density, $width, $height),
            'FBLC' => Constants::USER_AGENT_LOCALE,
            'FBCR' => '', // We don't have cellular.
            'FBMF' => $this->_escapeFbString($device->getManufacturer()),
            'FBBD' => $this->_escapeFbString($device->getBrand() ? $device->getBrand() : $device->getManufacturer()),
            'FBPN' => Constants::PACKAGE_NAME,
            'FBDV' => $this->_escapeFbString($device->getModel()),
            'FBSV' => $this->_escapeFbString($device->getAndroidRelease()),
            'FBBK' => 1, // Const (at least in 10.12.0).
            'FBCA' => $this->_escapeFbString(GoodDevices::CPU_ABI),
        ];
        array_walk($result, function (&$value, $key) {
            $value = sprintf('%s/%s', $key, $value);
        });

        // Trailing semicolon is essential.
        return '['.implode(';', $result).';]';
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
            'User-Agent'    => $this->_instagram->device->getUserAgent(),
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
            'a'                 => $this->_getFbUserAgent(),
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
            'd'                 => strtolower($this->_instagram->uuid),
            // DEVICE_SECRET
            'ds'                => '',
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
            json_encode('%ACCOUNT_ID_'.$randNum.'%') => $this->_instagram->account_id,
            json_encode('%SESSION_ID_'.$randNum.'%') => round($sessionId),
        ]);

        return $result;
    }

    /**
     * Returns password for MQTT connection.
     *
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     *
     * @return string
     */
    protected function _getMqttPassword()
    {
        $cookie = $this->_instagram->client->getCookie('sessionid', 'i.instagram.com');
        if ($cookie !== null) {
            return sprintf('%s=%s', $cookie->getName(), $cookie->getValue());
        }
        throw new \RuntimeException('No session cookie was found.');
    }

    /**
     * Returns client's identifier for MQTT connection.
     *
     * @return string
     */
    protected function _getMqttClientId()
    {
        return strtolower(substr($this->_instagram->uuid, 0, 20));
    }

    /**
     * @param ReactMqttClient $connection
     */
    protected function _beforeConnect(
        ReactMqttClient $connection)
    {
        $connection->on('open', function () {
            $this->debug('Connection has been established');
        });
        $connection->on('close', function () {
            $this->debug('Connection has been closed');
            $this->_connection = null;
            $this->_isConnected = false;
            $this->connect();
        });
        $connection->on('connect', function () {
            $this->debug('Connected to broker');
        });
    }

    /**
     * @param ReactMqttClient $connection
     */
    protected function _afterConnect(
        ReactMqttClient $connection)
    {
        $connection->on('ping', function () {
            $this->debug('PINGRESP received');
            $this->setKeepaliveTimer(self::MQTT_KEEPALIVE);
        });
        $connection->on('pong', function () {
            $this->debug('PINGRESP sent');
            $this->setKeepaliveTimer(self::MQTT_KEEPALIVE);
        });
        $connection->on('publish', function () {
            $this->debug('Publish flow completed');
            $this->setKeepaliveTimer(self::MQTT_KEEPALIVE);
        });
        $connection->on('subscribe', function () {
            $this->debug('Subscribe flow completed');
            $this->setKeepaliveTimer(self::MQTT_KEEPALIVE);
        });
        $connection->on('unsubscribe', function () {
            $this->debug('Unsubscribe flow completed');
            $this->setKeepaliveTimer(self::MQTT_KEEPALIVE);
        });
        $connection->on('message', function (Message $message) {
            $this->setKeepaliveTimer(self::MQTT_KEEPALIVE);
            $this->_onReceive($message);
        });
        $connection->on('disconnect', function () {
            $this->debug('Disconnected from broker');
        });
        $this->setKeepaliveTimer(self::MQTT_KEEPALIVE);
        $this->_connection = $connection;
        $this->_isConnected = true;
        $this->_subscribe();
    }

    /** {@inheritdoc} */
    protected function _connect()
    {
        $host = self::DEFAULT_HOST;
        $port = self::DEFAULT_PORT;
        $this->debug('Connecting to "%s:%d"', $host, $port);
        $connector = new Connector($this->_rtc->getLoop(), $this->getConnector($host, false), $this->getConnector($host, true));
        /** @var ReactMqttClient $connection */
        $connection = $connector(true);
        try {
            $password = $this->_getMqttPassword();
        } catch (\Exception $e) {
            $this->_rtc->emit('error', $e);

            return;
        }
        $this->_beforeConnect($connection);
        $connection->connect($host, $port, new DefaultConnection(
            $this->_getMqttUsername(),
            $password,
            null,
            $this->_getMqttClientId(),
            self::MQTT_KEEPALIVE,
            self::MQTT_VERSION,
            true
        ))->then(function () use ($connection) {
            $this->_afterConnect($connection);
        }, function (\Exception $e) {
            $this->debug($e->getMessage());
            $this->debug('Retrying connection attempt because of the error');
            $this->reconnect();
        });
    }

    /**
     * Subscribe to Iris.
     */
    protected function _subscribeToIris()
    {
        if (!$this->_irisEnabled || $this->_sequenceId === self::INVALID_SEQUENCE_ID) {
            return;
        }
        $this->debug('Subscribing to iris with sequence %d', $this->_sequenceId);
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
        $this->debug('Sequence updated to %d', $this->_sequenceId);
        if ($this->_isConnected) {
            $this->_subscribeToIris();
        }
    }

    /**
     * Subscribe to all topics.
     */
    protected function _subscribe()
    {
        if (count($this->_pubsubTopics)) {
            $this->debug('Subscribing to pubsub topics %s', implode(', ', $this->_pubsubTopics));
            $command = [
                'sub' => $this->_pubsubTopics,
            ];
            $this->_publish(self::PUBSUB_TOPIC, Realtime::jsonEncode($command), self::ACKNOWLEDGED_DELIVERY);
        }
        $this->_subscribeToIris();
        if (count($this->_graphqlTopics)) {
            $this->debug('Subscribing to graphql topics %s', implode(', ', $this->_graphqlTopics));
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
            $this->debug('Unsubscribing from pubsub topics %s', implode(', ', $this->_pubsubTopics));
            $command = [
                'unsub' => $this->_pubsubTopics,
            ];
            $this->_publish(self::PUBSUB_TOPIC, Realtime::jsonEncode($command), self::ACKNOWLEDGED_DELIVERY);
        }
        if (count($this->_graphqlTopics)) {
            $this->debug('Unsubscribing from graphql topics %s', implode(', ', $this->_graphqlTopics));
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
        if ($this->_connection === null) {
            return;
        }
        $this->debug('Sending message %s to topic %s', $payload, $topic);
        $payload = zlib_encode($payload, ZLIB_ENCODING_DEFLATE, 9);
        // We need to map human readable topic name to its ID because of bandwidth saving.
        $topic = $this->_mapTopic($topic);
        $this->_connection->publish(new DefaultMessage($topic, $payload, $qosLevel));
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
        $payload = zlib_decode($msg->getPayload());
        if ($payload === false) {
            $this->debug('Failed to inflate payload');

            return;
        }
        switch ($topic) {
            case self::PUBSUB_TOPIC:
            case self::PUBSUB_TOPIC_ID:
                $skywalker = new Client\Mqtt\Skywalker($payload);
                if (!in_array($skywalker->getType(), [Client\Mqtt\Skywalker::TYPE_DIRECT, Client\Mqtt\Skywalker::TYPE_LIVE])) {
                    $this->debug('Received skywalker message with unsupported type %d', $skywalker->getType());

                    return;
                }
                $payload = $skywalker->getPayload();
                break;
            case self::GRAPHQL_TOPIC:
            case self::GRAPHQL_TOPIC_ID:
            case self::REALTIME_SUB_TOPIC:
            case self::REALTIME_SUB_TOPIC_ID:
                $graphQl = new Client\Mqtt\GraphQl($payload);
                if (!in_array($graphQl->getTopic(), [Client\Mqtt\GraphQl::TOPIC_DIRECT])) {
                    $this->debug('Received graphql message with unsupported topic %s', $graphQl->getTopic());

                    return;
                }
                $payload = $graphQl->getPayload();
                break;
            case self::IRIS_SUB_RESPONSE_TOPIC:
            case self::IRIS_SUB_RESPONSE_TOPIC_ID:
                $json = HttpClient::api_body_decode($payload);
                if (!is_object($json)) {
                    $this->debug('Failed to decode Iris JSON: %s', json_last_error_msg());

                    return;
                }
                /** @var Client\Mqtt\Iris $iris */
                $iris = $this->mapToJson($json, new Client\Mqtt\Iris());
                if (!$iris->isSucceeded()) {
                    $this->debug('Failed to subscribe to Iris (%d): %s', $iris->getErrorType(), $iris->getErrorMessage());
                }

                return;
            case self::SEND_MESSAGE_RESPONSE_TOPIC:
            case self::SEND_MESSAGE_RESPONSE_TOPIC_ID:
            case self::MESSAGE_SYNC_TOPIC:
            case self::MESSAGE_SYNC_TOPIC_ID:
                break;
            default:
                $this->debug('Received message from unsupported topic "%s"', $topic);

                return;
        }
        $this->_processMessage($payload);
    }

    /** {@inheritdoc} */
    protected function _sendCommand(
        $command)
    {
        $this->_publish(self::SEND_MESSAGE_TOPIC, $command, self::FIRE_AND_FORGET);

        return true;
    }
}
