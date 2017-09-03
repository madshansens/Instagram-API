<?php

namespace InstagramAPI\React;

use InstagramAPI\Instagram;
use InstagramAPI\Utils;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use React\Socket\ConnectorInterface;

class Connector implements ConnectorInterface
{
    /**
     * @var Instagram
     */
    protected $_instagram;

    /**
     * @var LoopInterface
     */
    protected $_loop;

    /**
     * @var ConnectorInterface[]
     */
    protected $_connectors;

    /**
     * Connector constructor.
     *
     * @param Instagram     $instagram
     * @param LoopInterface $loop
     */
    public function __construct(
        Instagram $instagram,
        LoopInterface $loop)
    {
        $this->_instagram = $instagram;
        $this->_loop = $loop;

        $this->_connectors = [];
    }

    /**
     * @param string $uri
     *
     * @return PromiseInterface
     */
    public function connect(
        $uri)
    {
        $host = parse_url($uri, PHP_URL_HOST);
        if (!isset($this->_connectors[$host])) {
            $this->_connectors[$host] = Utils::getSecureConnector(
                $this->_loop,
                Utils::getSecureContext($this->_instagram->getVerifySSL()),
                Utils::getProxyForHost($host, $this->_instagram->getProxy())
            );
        }
        /** @var PromiseInterface $promise */
        $promise = $this->_connectors[$host]->connect($uri);

        return $promise;
    }
}
