<?php

namespace InstagramAPI\Realtime\Client\WebSocket;

use Ratchet\Client\Connector as RatchetConnector;
use Ratchet\RFC6455\Handshake\ClientNegotiator;
use React\SocketClient\ConnectorInterface;

class Connector extends RatchetConnector
{
    /**
     * Constructor. Parent constructor call is omitted intentionally.
     *
     * @param ConnectorInterface $connector
     * @param ConnectorInterface $secureConnector
     */
    public function __construct(
        ConnectorInterface $connector,
        ConnectorInterface $secureConnector)
    {
        $this->_connector = $connector;
        $this->_secureConnector = $secureConnector;
        $this->_negotiator = new ClientNegotiator();
    }
}
