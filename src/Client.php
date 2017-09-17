<?php

namespace InstagramAPI;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\HandlerStack;
use InstagramAPI\Exception\ServerMessageThrower;
use InstagramAPI\Exception\SettingsException;
use Psr\Http\Message\RequestInterface as HttpRequestInterface;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;
use function GuzzleHttp\Psr7\modify_request;

/**
 * This class handles core API network communication.
 *
 * WARNING TO CONTRIBUTORS: This class is a wrapper for the HTTP client, and
 * handles raw networking, cookies, HTTP requests and responses. Don't put
 * anything related to high level API functions (such as file uploads) here.
 * Most of the higher level code belongs in either the Request class or in the
 * individual endpoint functions.
 *
 * @author mgp25: Founder, Reversing, Project Leader (https://github.com/mgp25)
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class Client
{
    /**
     * How frequently we're allowed to auto-save the cookie jar, in seconds.
     *
     * @var int
     */
    const COOKIE_AUTOSAVE_INTERVAL = 45;

    /**
     * The Instagram class instance we belong to.
     *
     * @var \InstagramAPI\Instagram
     */
    protected $_parent;

    /**
     * What user agent to identify our client as.
     *
     * @var string
     */
    protected $_userAgent;

    /**
     * The SSL certificate verification behavior of requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @var bool|string
     */
    protected $_verifySSL;

    /**
     * Proxy to use for all requests. Optional.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @var string|array|null
     */
    protected $_proxy;

    /**
     * Network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see http://php.net/curl_setopt CURLOPT_INTERFACE
     *
     * @var string|null
     */
    protected $_outputInterface;

    /**
     * @var \GuzzleHttp\Client
     */
    private $_guzzleClient;

    /**
     * @var \InstagramAPI\ClientMiddleware
     */
    private $_clientMiddleware;

    /**
     * @var \GuzzleHttp\Cookie\CookieJar
     */
    private $_cookieJar;

    /**
     * The cookie format expected by the current settings storage.
     *
     * @var string
     */
    private $_settingsCookieFormat;

    /**
     * The disk path for file-based cookie jars.
     *
     * Only used when the cookieformat is set to "cookiefile".
     *
     * @var string|null
     */
    private $_settingsCookieFilePath;

    /**
     * The timestamp of when we last saved our cookie jar to disk.
     *
     * Used for automatically saving the jar after any API call, after enough
     * time has elapsed since our last save.
     *
     * @var int
     */
    private $_settingsCookieLastSaved;

    /**
     * Our JSON object mapper instance.
     *
     * This object must be globally preserved and always re-used, so that its
     * runtime class analysis cache stays in memory, otherwise it wastes time
     * analyzing our class source code every time it has to map a class again.
     *
     * @var \JsonMapper
     */
    private $_mapper;

    /**
     * Constructor.
     *
     * @param \InstagramAPI\Instagram $parent
     */
    public function __construct(
        $parent)
    {
        $this->_parent = $parent;

        // Defaults.
        $this->_verifySSL = true;
        $this->_proxy = null;

        // Create a default handler stack with Guzzle's auto-selected "best
        // possible transfer handler for the user's system", and with all of
        // Guzzle's default middleware (cookie jar support, etc).
        $stack = HandlerStack::create();

        // Create our custom Guzzle client middleware and add it to the stack.
        $this->_clientMiddleware = new ClientMiddleware();
        $stack->push($this->_clientMiddleware);

        // Default request options (immutable after client creation).
        $this->_guzzleClient = new GuzzleClient([
            'handler'         => $stack, // Our middleware is now injected.
            'allow_redirects' => [
                'max' => 8, // Allow up to eight redirects (that's plenty).
            ],
            'connect_timeout' => 30.0, // Give up trying to connect after 30s.
            'decode_content'  => true, // Decode gzip/deflate/etc HTTP responses.
            'timeout'         => 240.0, // Maximum per-request time (seconds).
            // Tells Guzzle to stop throwing exceptions on non-"2xx" HTTP codes,
            // thus ensuring that it only triggers exceptions on socket errors!
            // We'll instead MANUALLY be throwing on certain other HTTP codes.
            'http_errors'     => false,
        ]);

        // Create our JSON object mapper and set global default options.
        $this->_mapper = new \JsonMapper();
        $this->_mapper->bStrictNullTypes = false; // Allow NULL values.
    }

    /**
     * Resets certain Client settings via the current Settings storage.
     *
     * Used whenever we switch active user, to configure our internal state.
     *
     * @param bool $resetCookieJar (optional) Whether to clear current cookies.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function updateFromCurrentSettings(
        $resetCookieJar = false)
    {
        // Update our internal client state from the new user's settings.
        $this->_userAgent = $this->_parent->device->getUserAgent();
        $this->loadCookieJar($resetCookieJar);

        // Verify that the jar contains a non-expired csrftoken for the API
        // domain. Instagram gives us a 1-year csrftoken whenever we log in.
        // If it's missing, we're definitely NOT logged in! But even if all of
        // these checks succeed, the cookie may still not be valid. It's just a
        // preliminary check to detect definitely-invalid session cookies!
        if ($this->getToken() === null) {
            $this->_parent->isLoggedIn = false;
        }
    }

    /**
     * Loads all cookies via the current Settings storage.
     *
     * @param bool $resetCookieJar (optional) Whether to clear current cookies.
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function loadCookieJar(
        $resetCookieJar = false)
    {
        // Mark any previous cookie jar for garbage collection.
        $this->_cookieJar = null;

        // Get all cookies for the currently active user.
        $userCookies = $this->_parent->settings->getCookies();
        $this->_settingsCookieFormat = $userCookies['format'];
        $this->_settingsCookieFilePath = null;

        // Get the raw cookie string from the storage backend.
        $cookieString = '';
        if ($userCookies['format'] == 'cookiefile') {
            $this->_settingsCookieFilePath = $userCookies['data'];
            if (empty($this->_settingsCookieFilePath)) {
                throw new SettingsException(
                    'Cookie file format requested, but no file path provided.'
                );
            }

            // Ensure that the whole directory path to the cookie file exists.
            $cookieDir = dirname($this->_settingsCookieFilePath); // Can be "." in case of CWD.
            if (!Utils::createFolder($cookieDir)) {
                throw new SettingsException(sprintf(
                    'The "%s" cookie folder is not writable.',
                    $cookieDir
                ));
            }

            // Process the existing cookie jar file if it already exists.
            if (is_file($this->_settingsCookieFilePath)) {
                if ($resetCookieJar) {
                    // Delete existing cookie jar since this is a reset.
                    @unlink($this->_settingsCookieFilePath);
                } else {
                    // Read the existing cookies from disk.
                    $rawData = file_get_contents($this->_settingsCookieFilePath);
                    if ($rawData !== false) {
                        $cookieString = $rawData;
                    }
                }
            }
        } else {
            // Delete existing cookie data from the storage if this is a reset.
            if ($resetCookieJar) {
                $userCookies['data'] = '';
                $this->_parent->settings->setCookies('');
            }

            // Read the existing cookies provided by the storage.
            $cookieString = $userCookies['data'];
        }

        // Attempt to restore the cookies, otherwise create a new, empty jar.
        $restoredCookies = @json_decode($cookieString, true);
        if (!is_array($restoredCookies)) {
            $restoredCookies = [];
        }

        // Memory-based cookie jar which must be manually saved later.
        $this->_cookieJar = new CookieJar(false, $restoredCookies);

        // Reset the "last saved" timestamp to the current time to prevent
        // auto-saving the cookies again immediately after this jar is loaded.
        $this->_settingsCookieLastSaved = time();
    }

    /**
     * Retrieve the CSRF token from the current cookie jar.
     *
     * Note that Instagram gives you a 1-year token expiration timestamp when
     * you log in. But if you log out, they set its timestamp to "0" which means
     * that the cookie is "expired" and invalid. We ignore token cookies if they
     * have been logged out, or if they have expired naturally.
     *
     * @return string|null The token if found and non-expired, otherwise NULL.
     */
    public function getToken()
    {
        $cookie = $this->getCookie('csrftoken', 'i.instagram.com');
        if ($cookie === null || $cookie->getExpires() <= time()) {
            return; // Ugh, StyleCI doesn't allow "return null;" for clarity. ;)
        }

        return $cookie->getValue();
    }

    /**
     * Searches for a specific cookie in the current jar.
     *
     * @param string      $name   The name of the cookie.
     * @param string|null $domain (optional) Require a specific domain match.
     * @param string|null $path   (optional) Require a specific path match.
     *
     * @return \GuzzleHttp\Cookie\SetCookie|null A cookie if found, otherwise NULL.
     */
    public function getCookie(
        $name,
        $domain = null,
        $path = null)
    {
        $foundCookie = null;
        if ($this->_cookieJar instanceof CookieJar) {
            foreach ($this->_cookieJar->getIterator() as $cookie) {
                if ($cookie->getName() == $name
                    && ($domain === null || $cookie->getDomain() == $domain)
                    && ($path === null || $cookie->getPath() == $path)) {
                    $foundCookie = $cookie;
                    break;
                }
            }
        }

        return $foundCookie;
    }

    /**
     * Gives you all cookies in the Jar encoded as a JSON string.
     *
     * This allows custom Settings storages to retrieve all cookies for saving.
     *
     * @throws \InvalidArgumentException If the JSON cannot be encoded.
     *
     * @return string
     */
    public function getCookieJarAsJSON()
    {
        if (!$this->_cookieJar instanceof CookieJar) {
            return '[]';
        }

        // Gets ALL cookies from the jar, even temporary session-based cookies.
        $cookies = $this->_cookieJar->toArray();

        // Throws if data can't be encoded as JSON (will never happen).
        $jsonStr = \GuzzleHttp\json_encode($cookies);

        return $jsonStr;
    }

    /**
     * Tells current settings storage to store cookies if necessary.
     *
     * NOTE: This Client class is NOT responsible for calling this function!
     * Instead, our parent "Instagram" instance takes care of it and saves the
     * cookies "onCloseUser", so that cookies are written to storage in a
     * single, efficient write when the user's session is finished. We also call
     * it during some important function calls such as login/logout. Client also
     * automatically calls it when enough time has elapsed since last save.
     *
     * @throws \InvalidArgumentException                 If the JSON cannot be encoded.
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function saveCookieJar()
    {
        $newCookies = $this->getCookieJarAsJSON();
        if ($this->_settingsCookieFormat != 'cookiefile') {
            // Tell non-file settings storage to persist the latest cookies.
            $this->_parent->settings->setCookies($newCookies);
        } else {
            // This is a file-based cookie storage. It's our job to write it.
            if (!empty($this->_settingsCookieFilePath)) {
                // Perform an atomic diskwrite, which prevents accidental
                // truncation if the script is ever interrupted mid-write.
                $written = Utils::atomicWrite($this->_settingsCookieFilePath, $newCookies);
                if ($written === false) {
                    throw new SettingsException(sprintf(
                        'The "%s" cookie file is not writable.',
                        $this->_settingsCookieFilePath
                    ));
                }
            }
        }

        // Reset the "last saved" timestamp to the current time.
        $this->_settingsCookieLastSaved = time();
    }

    /**
     * Controls the SSL verification behavior of the Client.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @param bool|string $state TRUE to verify using PHP's default CA bundle,
     *                           FALSE to disable SSL verification (this is
     *                           insecure!), String to verify using this path to
     *                           a custom CA bundle file.
     */
    public function setVerifySSL(
        $state)
    {
        $this->_verifySSL = $state;
    }

    /**
     * Gets the current SSL verification behavior of the Client.
     *
     * @return bool|string
     */
    public function getVerifySSL()
    {
        return $this->_verifySSL;
    }

    /**
     * Set the proxy to use for requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @param string|array|null $value String or Array specifying a proxy in
     *                                 Guzzle format, or NULL to disable proxying.
     */
    public function setProxy(
        $value)
    {
        $this->_proxy = $value;
    }

    /**
     * Gets the current proxy used for requests.
     *
     * @return string|array|null
     */
    public function getProxy()
    {
        return $this->_proxy;
    }

    /**
     * Sets the network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see http://php.net/curl_setopt CURLOPT_INTERFACE
     *
     * @param string|null $value Interface name, IP address or hostname, or NULL to
     *                           disable override and let Guzzle use any interface.
     */
    public function setOutputInterface(
        $value)
    {
        $this->_outputInterface = $value;
    }

    /**
     * Gets the current network interface override used for requests.
     *
     * @return string|null
     */
    public function getOutputInterface()
    {
        return $this->_outputInterface;
    }

    /**
     * Output debugging information.
     *
     * @param string      $method        "GET" or "POST".
     * @param string      $url           The URL or endpoint used for the request.
     * @param string|null $uploadedBody  What was sent to the server. Use NULL to
     *                                   avoid displaying it.
     * @param int|null    $uploadedBytes How many bytes were uploaded. Use NULL to
     *                                   avoid displaying it.
     * @param object      $response      The Guzzle response object from the request.
     * @param string      $responseBody  The actual text-body reply from the server.
     */
    protected function _printDebug(
        $method,
        $url,
        $uploadedBody,
        $uploadedBytes,
        $response,
        $responseBody)
    {
        Debug::printRequest($method, $url);

        // Display the data body that was uploaded, if provided for debugging.
        // NOTE: Only provide this from functions that submit meaningful BODY data!
        if (is_string($uploadedBody)) {
            Debug::printPostData($uploadedBody);
        }

        // Display the number of bytes uploaded in the data body, if provided for debugging.
        // NOTE: Only provide this from functions that actually upload files!
        if ($uploadedBytes !== null) {
            Debug::printUpload(Utils::formatBytes($uploadedBytes));
        }

        // Display the number of bytes received from the response, and status code.
        if ($response->hasHeader('x-encoded-content-length')) {
            $bytes = Utils::formatBytes($response->getHeader('x-encoded-content-length')[0]);
        } else {
            $bytes = Utils::formatBytes($response->getHeader('Content-Length')[0]);
        }
        Debug::printHttpCode($response->getStatusCode(), $bytes);

        // Display the actual API response body.
        Debug::printResponse($responseBody, $this->_parent->truncatedDebug);
    }

    /**
     * Converts a server response to a specific kind of result object.
     *
     * @param ResponseInterface     $baseClass      An instance of a class object whose
     *                                              properties to fill with the response.
     * @param mixed                 $serverResponse A decoded JSON response from
     *                                              Instagram's server.
     * @param HttpResponseInterface $httpResponse   HTTP response object.
     *
     * @throws \InstagramAPI\Exception\InstagramException In case of invalid or
     *                                                    failed API response.
     *
     * @return ResponseInterface
     */
    public function getMappedResponseObject(
        ResponseInterface $baseClass,
        $serverResponse,
        HttpResponseInterface $httpResponse)
    {
        // If the server response is not an object, it means that JSON decoding
        // failed or some other bad thing happened. So analyze the HTTP status
        // code (if available) to see what really happened.
        if (!is_object($serverResponse)) {
            $httpStatusCode = $httpResponse !== null ? $httpResponse->getStatusCode() : null;
            switch ($httpStatusCode) {
                case 400:
                    throw new \InstagramAPI\Exception\BadRequestException('Invalid request options.');
                case 404:
                    throw new \InstagramAPI\Exception\NotFoundException('Requested resource does not exist.');
                default:
                    throw new \InstagramAPI\Exception\EmptyResponseException('No response from server. Either a connection or configuration error.');
            }
        }

        // Use API developer debugging? Throws if class lacks properties.
        $this->_mapper->bExceptionOnUndefinedProperty = $this->_parent->apiDeveloperDebug;

        // Perform mapping of all response properties.
        /** @var ResponseInterface $responseObject */
        $responseObject = $this->_mapper->map($serverResponse, $baseClass);

        // Save the raw response object as the "getFullResponse()" value.
        $responseObject->setFullResponse($serverResponse);

        // Save the HTTP response object as the "getHttpResponse()" value.
        $responseObject->setHttpResponse($httpResponse);

        // Throw an exception if the API response was unsuccessful.
        // NOTE: It will contain the full server response object too, which
        // means that the user can look at the full response details via the
        // exception itself.
        if (!$responseObject->isOk()) {
            if ($responseObject instanceof \InstagramAPI\Response\DirectSendItemResponse && $responseObject->getPayload() !== null) {
                $message = $responseObject->getPayload()->getMessage();
            } else {
                $message = $responseObject->getMessage();
            }
            ServerMessageThrower::autoThrow(
                get_class($baseClass),
                $message,
                $responseObject,
                $httpResponse
            );
        }

        return $responseObject;
    }

    /**
     * Helper which builds in the most important Guzzle options.
     *
     * Takes care of adding all critical options that we need on every request.
     * Such as cookies and the user's proxy. But don't call this function
     * manually. It's automatically called by _guzzleRequest()!
     *
     * @param array $guzzleOptions The options specific to the current request.
     *
     * @return array A guzzle options array.
     */
    protected function _buildGuzzleOptions(
        array $guzzleOptions = [])
    {
        $criticalOptions = [
            'cookies' => ($this->_cookieJar instanceof CookieJar ? $this->_cookieJar : false),
            'verify'  => $this->_verifySSL,
            'proxy'   => ($this->_proxy !== null ? $this->_proxy : null),
        ];

        // Critical options always overwrite identical keys in regular opts.
        // This ensures that we can't screw up the proxy/verify/cookies.
        $finalOptions = array_merge($guzzleOptions, $criticalOptions);

        // Now merge any specific Guzzle cURL-backend overrides. We must do this
        // separately since it's in an associative array and we can't just
        // overwrite that whole array in case the caller had curl options.
        if (!array_key_exists('curl', $finalOptions)) {
            $finalOptions['curl'] = [];
        }

        // Add their network interface override if they want it.
        // This option MUST be non-empty if set, otherwise it breaks cURL.
        if (is_string($this->_outputInterface) && $this->_outputInterface !== '') {
            $finalOptions['curl'][CURLOPT_INTERFACE] = $this->_outputInterface;
        }

        return $finalOptions;
    }

    /**
     * Wraps Guzzle's request and adds special error handling and options.
     *
     * Automatically throws exceptions on certain very serious HTTP errors. And
     * re-wraps all Guzzle errors to our own internal exceptions instead. You
     * must ALWAYS use this (or _apiRequest()) instead of the raw Guzzle Client!
     * However, you can never assume the server response contains what you
     * wanted. Be sure to validate the API reply too, since Instagram's API
     * calls themselves may fail with a JSON message explaining what went wrong.
     *
     * WARNING: This is a semi-lowlevel handler which only applies critical
     * options and HTTP connection handling! Most functions will want to call
     * _apiRequest() instead. An even higher-level handler which takes care of
     * debugging, server response checking and response decoding!
     *
     * @param HttpRequestInterface $request       HTTP request to send.
     * @param array                $guzzleOptions Extra Guzzle options for this request.
     *
     * @throws \InstagramAPI\Exception\NetworkException   For any network/socket related errors.
     * @throws \InstagramAPI\Exception\ThrottledException When we're throttled by server.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function _guzzleRequest(
        HttpRequestInterface $request,
        array $guzzleOptions = [])
    {
        // Add critically important options for authenticating the request.
        $guzzleOptions = $this->_buildGuzzleOptions($guzzleOptions);

        // Attempt the request. Will throw in case of socket errors!
        try {
            $response = $this->_guzzleClient->send($request, $guzzleOptions);
        } catch (\Exception $e) {
            // Re-wrap Guzzle's exception using our own NetworkException.
            throw new \InstagramAPI\Exception\NetworkException($e);
        }

        // Detect very serious HTTP status codes in the response.
        $httpCode = $response->getStatusCode();
        switch ($httpCode) {
        case 429: // "429 Too Many Requests"
            throw new \InstagramAPI\Exception\ThrottledException('Throttled by Instagram because of too many API requests.');
            break;
        // WARNING: Do NOT detect 404 and other higher-level HTTP errors here,
        // since we catch those later during steps like getMappedResponseObject
        // and autoThrow. This is a warning to future contributors!
        }

        // We'll periodically auto-save our cookies at certain intervals. This
        // complements the "onCloseUser" and "login()/logout()" force-saving.
        if ((time() - $this->_settingsCookieLastSaved) > self::COOKIE_AUTOSAVE_INTERVAL) {
            $this->saveCookieJar();
        }

        // The response may still have serious but "valid response" errors, such
        // as "400 Bad Request". But it's up to the CALLER to handle those!
        return $response;
    }

    /**
     * Internal wrapper around _guzzleRequest().
     *
     * This takes care of many common additional tasks needed by our library,
     * so you should try to always use this instead of the raw _guzzleRequest()!
     *
     * Available library options are:
     * - 'noDebug': Can be set to TRUE to forcibly hide debugging output for
     *   this request. The user controls debugging globally, but this is an
     *   override that prevents them from seeing certain requests that you may
     *   not want to trigger debugging (such as perhaps individual steps of a
     *   file upload process). However, debugging SHOULD be allowed in MOST cases!
     *   So only use this feature if you have a very good reason.
     * - 'debugUploadedBody': Set to TRUE to make debugging display the data that
     *   was uploaded in the body of the request. DO NOT use this if your function
     *   uploaded binary data, since printing those bytes would kill the terminal!
     * - 'debugUploadedBytes': Set to TRUE to make debugging display the size of
     *   the uploaded body data. Should ALWAYS be TRUE when uploading binary data.
     *
     * @param HttpRequestInterface $request        HTTP request to send.
     * @param array                $guzzleOptions  Extra Guzzle options for this request.
     * @param array                $libraryOptions Additional options for controlling Library features
     *                                             such as the debugging output.
     *
     * @throws \InstagramAPI\Exception\NetworkException   For any network/socket related errors.
     * @throws \InstagramAPI\Exception\ThrottledException When we're throttled by server.
     *
     * @return HttpResponseInterface
     */
    protected function _apiRequest(
        HttpRequestInterface $request,
        array $guzzleOptions = [],
        array $libraryOptions = [])
    {
        // Perform the API request and retrieve the raw HTTP response body.
        $guzzleResponse = $this->_guzzleRequest($request, $guzzleOptions);

        // Debugging (must be shown before possible decoding error).
        if ($this->_parent->debug && (!isset($libraryOptions['noDebug']) || !$libraryOptions['noDebug'])) {
            // Determine whether we should display the contents of the UPLOADED body.
            if (isset($libraryOptions['debugUploadedBody']) && $libraryOptions['debugUploadedBody']) {
                $uploadedBody = (string) $request->getBody();
                if (!strlen($uploadedBody)) {
                    $uploadedBody = null;
                }
            } else {
                $uploadedBody = null; // Don't display.
            }

            // Determine whether we should display the size of the UPLOADED body.
            if (isset($libraryOptions['debugUploadedBytes']) && $libraryOptions['debugUploadedBytes']) {
                // Calculate the uploaded bytes by looking at request's body size, if it exists.
                $uploadedBytes = $request->getBody()->getSize();
            } else {
                $uploadedBytes = null; // Don't display.
            }

            $this->_printDebug(
                $request->getMethod(),
                (string) $request->getUri(),
                $uploadedBody,
                $uploadedBytes,
                $guzzleResponse,
                (string) $guzzleResponse->getBody());
        }

        return $guzzleResponse;
    }

    /**
     * Perform an Instagram API call.
     *
     * @param HttpRequestInterface $request       HTTP request to send.
     * @param array                $guzzleOptions Extra Guzzle options for this request.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return HttpResponseInterface
     */
    public function api(
        HttpRequestInterface $request,
        array $guzzleOptions = [])
    {
        // Set up headers that are required for every request.
        $request = modify_request($request, [
            'set_headers' => [
                'User-Agent'       => $this->_userAgent,
                // Keep the API's HTTPS connection alive in Guzzle for future
                // re-use, to greatly speed up all further queries after this.
                'Connection'       => 'Keep-Alive',
                'X-FB-HTTP-Engine' => Constants::X_FB_HTTP_Engine,
                'Accept'           => '*/*',
                'Accept-Encoding'  => Constants::ACCEPT_ENCODING,
                'Accept-Language'  => Constants::ACCEPT_LANGUAGE,
            ],
        ]);

        // Check the Content-Type header for debugging.
        $contentType = $request->getHeader('Content-Type');
        $isFormData = count($contentType) && reset($contentType) === Constants::CONTENT_TYPE;

        // Perform the API request.
        $response = $this->_apiRequest($request, $guzzleOptions, [
            'debugUploadedBody'  => $isFormData,
            'debugUploadedBytes' => !$isFormData,
        ]);

        return $response;
    }

    /**
     * Decode a JSON reply from Instagram's API.
     *
     * WARNING: EXTREMELY IMPORTANT! NEVER, *EVER* USE THE BASIC "json_decode"
     * ON API REPLIES! ALWAYS USE THIS METHOD INSTEAD, TO ENSURE PROPER DECODING
     * OF BIG NUMBERS! OTHERWISE YOU'LL TRUNCATE VARIOUS INSTAGRAM API FIELDS!
     *
     * @param string $json  The body (JSON string) of the API response.
     * @param bool   $assoc When TRUE, decode to associative array instead of object.
     *
     * @return object|array|null Object if assoc false, Array if assoc true,
     *                           or NULL if unable to decode JSON.
     */
    public static function api_body_decode(
        $json,
        $assoc = false)
    {
        return json_decode($json, $assoc, 512, JSON_BIGINT_AS_STRING);
    }

    /**
     * Get the client middleware instance.
     *
     * @return ClientMiddleware
     */
    public function getMiddleware()
    {
        return $this->_clientMiddleware;
    }
}
