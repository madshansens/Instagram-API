<?php

/*
 * IMPORTANT!
 * You need https://github.com/reactphp/http in order to run this example.
 * $ composer require react/socket "^0.5"
 * $ composer require react/http "^0.6"
 *
 * Usage:
 * # mark item 456 in thread 123 as seen
 * $ curl -i 'http://127.0.0.1:1307/seen?threadId=123&threadItemId=456'
 * # send typing notification to thread 123
 * $ curl -i 'http://127.0.0.1:1307/activity?threadId=123&flag=1'
 * # send some message to thread 123
 * $ curl -i 'http://127.0.0.1:1307/message?threadId=123&text=Hi!'
 * # like item 456 from thread 123
 * $ curl -i 'http://127.0.0.1:1307/likeItem?threadId=123&threadItemId=456'
 * # unlike item 456 from thread 123
 * $ curl -i 'http://127.0.0.1:1307/unlikeItem?threadId=123&threadItemId=456'
 * # send like to thread 123
 * $ curl -i 'http://127.0.0.1:1307/like?threadId=123'
 * # remove typing notification from thread 123
 * $ curl -i 'http://127.0.0.1:1307/activity?threadId=123&flag=0'
 * # ping realtime http server
 * $ curl -i 'http://127.0.0.1:1307/ping'
 * # stop realtime http server
 * $ curl -i 'http://127.0.0.1:1307/stop'
 */

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);
try {
    $ig->setUser($username, $password);
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

// Create main event loop.
$loop = \React\EventLoop\Factory::create();
// Create HTTP server along with Realtime client.
$httpServer = new RealtimeHttpServer($loop, $ig);
// Run main loop.
$loop->run();

class RealtimeHttpServer
{
    const HOST = '127.0.0.1';
    const PORT = 1307;

    const TIMEOUT = 5;

    /** @var \React\Promise\Deferred[] */
    protected $_contexts;

    /** @var \React\EventLoop\LoopInterface */
    protected $_loop;

    /** @var \InstagramAPI\Instagram */
    protected $_instagram;

    /** @var \InstagramAPI\Realtime */
    protected $_rtc;

    /** @var \React\Http\Server */
    protected $_server;

    /**
     * Constructor.
     *
     * @param \React\EventLoop\LoopInterface $loop
     * @param \InstagramAPI\Instagram        $instagram
     */
    public function __construct(
        \React\EventLoop\LoopInterface $loop,
        \InstagramAPI\Instagram $instagram)
    {
        $this->_loop = $loop;
        $this->_instagram = $instagram;
        $this->_contexts = [];
        $this->_rtc = new \InstagramAPI\Realtime($this->_instagram, $this->_loop);
        $this->_rtc->init()->then([$this, 'onRealtimeReady'], [$this, 'onRealtimeFail']);
    }

    /**
     * Gracefully stop everything.
     */
    protected function _stop()
    {
        // Initiate shutdown sequence.
        $this->_rtc->stop();
        // Wait 2 seconds for Realtime to shutdown.
        $this->_loop->addTimer(2, function () {
            // Stop main loop.
            $this->_loop->stop();
        });
    }

    /**
     * Called when fatal error has been received from Realtime.
     *
     * @param Exception $e
     */
    public function onRealtimeFail(
        \Exception $e)
    {
        printf('[!!!] Got fatal error from Realtime: %s%s', $e->getMessage(), PHP_EOL);
        $this->_stop();
    }

    /**
     * Called when ACK has been received.
     *
     * @param \InstagramAPI\Realtime\Action\Ack $ack
     */
    public function onClientContextAck(
        \InstagramAPI\Realtime\Action\Ack $ack)
    {
        printf('[RTC] Received ACK for %s with status %s%s', $ack->payload->client_context, $ack->status, PHP_EOL);
        // Check if we have deferred object for this client_context.
        $context = $ack->payload->client_context;
        if (!isset($this->_contexts[$context])) {
            return;
        }
        // Resolve deferred object with $ack.
        $deferred = $this->_contexts[$context];
        $deferred->resolve($ack);
        // Clean up.
        unset($this->_contexts[$context]);
    }

    /**
     * Called when Realtime is ready to run.
     */
    public function onRealtimeReady()
    {
        // Bind event listener for acknowledgements.
        $this->_rtc->on('client-context-ack', [$this, 'onClientContextAck']);
        $this->_rtc->on('error', [$this, 'onRealtimeFail']);
        $this->_rtc->start();
        $this->_startHttpServer();
    }

    /**
     * @param \React\Http\Response $response
     * @param string|null          $data
     */
    protected function _handleSuccess(
        \React\Http\Response $response,
        $data = null)
    {
        // Reply with 200 OK.
        $response->writeHead(200);
        $response->end($data);
    }

    /**
     * @param \React\Http\Response $response
     */
    protected function _handleSendingFail(
        \React\Http\Response $response)
    {
        // If we are not able to send command, reply with 503 Service Unavailable.
        $response->writeHead(503);
        $response->end();
    }

    /**
     * @param string               $context
     * @param \React\Http\Response $response
     */
    protected function _handleClientContext(
        $context,
        \React\Http\Response $response)
    {
        // Set up deferred object.
        $deferred = new \React\Promise\Deferred();
        $this->_contexts[$context] = $deferred;
        // Reject deferred after given timeout.
        $timeout = $this->_loop->addTimer(self::TIMEOUT, function () use ($deferred, $context) {
            $deferred->reject();
            unset($this->_contexts[$context]);
        });
        // Set up promise.
        $deferred->promise()
            ->then(function (\InstagramAPI\Realtime\Action\Ack $ack) use ($timeout, $response) {
                // Cancel reject timer.
                $timeout->cancel();
                // Reply with info from $ack.
                $response->writeHead($ack->status_code, ['Content-Type' => 'text/json']);
                $response->end(json_encode($ack->payload));
            })
            ->otherwise(function () use ($response) {
                // Called by reject timer. Reply with 504 Gateway Time-out.
                $response->writeHead(504);
                $response->end();
            });
    }

    /**
     * Handler for incoming HTTP requests.
     *
     * @param \React\Http\Request  $request
     * @param \React\Http\Response $response
     */
    public function onHttpRequest(
        \React\Http\Request $request,
        \React\Http\Response $response)
    {
        // Params validation is up to you.
        $params = $request->getQueryParams();
        // Treat request path as command.
        switch ($request->getPath()) {
            case '/ping':
                $this->_handleSuccess($response, 'pong');
                break;
            case '/stop':
                $this->_handleSuccess($response);
                $this->_stop();
                break;
            case '/seen':
                $context = $this->_rtc->markDirectItemSeen($params['threadId'], $params['threadItemId']);
                if ($context) {
                    $this->_handleSuccess($response);
                } else {
                    $this->_handleSendingFail($response);
                }
                break;
            case '/activity':
                $context = $this->_rtc->indicateActivityInDirectThread($params['threadId'], (bool) $params['flag']);
                if ($context !== false) {
                    $this->_handleClientContext($context, $response);
                } else {
                    $this->_handleSendingFail($response);
                }
                break;
            case '/message':
                $context = $this->_rtc->sendTextToDirect($params['threadId'], $params['text']);
                if ($context !== false) {
                    $this->_handleClientContext($context, $response);
                } else {
                    $this->_handleSendingFail($response);
                }
                break;
            case '/like':
                $context = $this->_rtc->sendLikeToDirect($params['threadId']);
                if ($context !== false) {
                    $this->_handleClientContext($context, $response);
                } else {
                    $this->_handleSendingFail($response);
                }
                break;
            case '/likeItem':
                $context = $this->_rtc->sendReactionToDirect($params['threadId'], $params['threadItemId'], 'like');
                if ($context !== false) {
                    $this->_handleClientContext($context, $response);
                } else {
                    $this->_handleSendingFail($response);
                }
                break;
            case '/unlikeItem':
                $context = $this->_rtc->deleteReactionFromDirect($params['threadId'], $params['threadItemId'], 'like');
                if ($context !== false) {
                    $this->_handleClientContext($context, $response);
                } else {
                    $this->_handleSendingFail($response);
                }
                break;
            default:
                // If command is unknown, reply with 404 Not Found.
                $response->writeHead(404);
                $response->end();
        }
    }

    /**
     * Init and start HTTP server.
     */
    protected function _startHttpServer()
    {
        // Create server socket.
        $socket = new \React\Socket\Server(self::HOST.':'.self::PORT, $this->_loop);
        printf('[RTC] Listening on http://%s%s', $socket->getAddress(), PHP_EOL);
        // Bind HTTP server on server socket.
        $this->_server = new \React\Http\Server($socket);
        $this->_server->on('request', [$this, 'onHttpRequest']);
    }
}
