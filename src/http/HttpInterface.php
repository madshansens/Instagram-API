<?php

namespace InstagramAPI;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;

class HttpInterface
{
    /**
     * The Instagram class instance we belong to.
     *
     * @var \InstagramAPI\Instagram
     */
    protected $parent;

    /**
     * What user agent to identify our client as.
     *
     * @var string
     */
    protected $userAgent;

    /**
     * The SSL certificate verification behavior of requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @var bool|string
     */
    protected $verifySSL;

    /**
     * Proxy to use for all requests. Optional.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @var string|array|null
     */
    protected $proxy;

    /**
     * Network interface to use.
     *
     * @TODO NOT IMPLEMENTED! Does nothing.
     *
     * @var string
     */
    public $outputInterface;

    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var \GuzzleHttp\Cookie\FileCookieJar|\GuzzleHttp\Cookie\CookieJar
     */
    private $jar;

    /**
     * Constructor.
     *
     * @param \InstagramAPI\Instagram $parent
     */
    public function __construct($parent)
    {
        $this->parent = $parent;

        // Defaults.
        $this->verifySSL = true;
        $this->proxy = null;

        // Default request options (immutable after client creation).
        $this->client = new Client([
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
    }

    /**
     * Resets certain HttpInterface settings via the current SettingsAdapter.
     *
     * Used whenever the user switches setUser(), to configure our internal state.
     */
    public function updateFromSettingsAdapter()
    {
        $this->userAgent = $this->parent->settings->get('user_agent');
        $this->jar = null; // Mark old jar for garbage collection.
        $this->loadCookieJar();
    }

    /**
     * Loads all cookies via the current SettingsAdapter.
     */
    public function loadCookieJar()
    {
        if ($this->parent->settingsAdapter['type'] == 'file') {
            // File-based cookie jar, which also persists temporary session cookies.
            // The FileCookieJar saves to disk whenever its object is destroyed,
            // such as at the end of script or when calling updateFromSettingsAdapter().
            $this->jar = new FileCookieJar($this->parent->settings->cookiesPath, true);
        } else {
            $restoredCookies = @json_decode($this->parent->settings->get('cookies'), true);
            if (!is_array($restoredCookies)) {
                $restoredCookies = []; // Create new, empty jar if restore failed.
            }
            $this->jar = new CookieJar(false, $restoredCookies);
        }

        // Verify that the jar contains a non-expired csrftoken for the API
        // domain. Instagram gives us a 1-year csrftoken whenever we log in.
        // If it's missing, we're definitely NOT logged in! But even if all of
        // these checks succeed, the cookie may still not be valid. It's just a
        // preliminary check to detect definitely-invalid session cookies!
        $foundCSRFToken = false;
        foreach ($this->jar->getIterator() as $cookie) {
            if ($cookie->getName() == 'csrftoken'
                && $cookie->getDomain() == 'i.instagram.com'
                && $cookie->getExpires() > time()) {
                $foundCSRFToken = true;
                break;
            }
        }
        if (!$foundCSRFToken) {
            $this->parent->isLoggedIn = false;
        }
    }

    /**
     * Gives you all cookies in the Jar encoded as a JSON string.
     *
     * This allows custom SettingsAdapters to retrieve all cookies for saving.
     *
     * @throws \InvalidArgumentException if the JSON cannot be encoded.
     *
     * @return string
     */
    public function getCookieJarAsJSON()
    {
        if (!$this->jar instanceof CookieJar) {
            return '[]';
        }

        // Gets ALL cookies from the jar, even temporary session-based cookies.
        $cookies = $this->jar->toArray();

        // Throws if data can't be encoded as JSON (will never happen).
        $jsonStr = \GuzzleHttp\json_encode($cookies);

        return $jsonStr;
    }

    /**
     * Tells current settings adapter to store cookies if necessary.
     *
     * There is no need to call this function manually. It's automatically
     * called by guzzleRequest()!
     */
    public function saveCookieJar()
    {
        // If it's a FileCookieJar, we don't have to do anything. They are saved
        // automatically to disk when that object is destroyed/garbage collected.
        if ($this->jar instanceof FileCookieJar) {
            return;
        }

        // Tell any custom settings adapters to persist the current cookies.
        if ($this->parent->settingsAdapter['type'] == 'mysql'
            || $this->parent->settings->setting instanceof SettingsAdapter\SettingsInterface) {
            $newCookies = $this->getCookieJarAsJSON();
            $this->parent->settings->set('cookies', $newCookies);
        }
    }

    /**
     * Controls the SSL verification behavior of the HttpInterface.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#verify
     *
     * @param bool|string $state TRUE to verify using PHP's default CA bundle,
     *                           FALSE to disable SSL verification (this is
     *                           insecure!), String to verify using this path to
     *                           a custom CA bundle file.
     */
    public function setVerifySSL($state)
    {
        $this->verifySSL = $state;
    }

    /**
     * Gets the current SSL verification behavior of the HttpInterface.
     *
     * @return bool|string
     */
    public function getVerifySSL()
    {
        return $this->verifySSL;
    }

    /**
     * Set the proxy to use for requests.
     *
     * @see http://docs.guzzlephp.org/en/latest/request-options.html#proxy
     *
     * @param string|array|null $value String or Array specifying a proxy in
     *                                 Guzzle format, or NULL to disable proxying.
     */
    public function setProxy($value)
    {
        $this->proxy = $value;
    }

    /**
     * Gets the current proxy used for requests.
     *
     * @return string|array|null
     */
    public function getProxy()
    {
        return $this->proxy;
    }

    /**
     * Output debugging information.
     *
     * @param string      $method       "GET" or "POST".
     * @param string      $url          The URL or endpoint used for the request.
     * @param string|null $postBody     What was sent to the server. Use NULL to
     *                                  avoid displaying it.
     * @param int|null    $uploadBytes  How many bytes were uploaded. Use NULL to
     *                                  avoid displaying it.
     * @param object      $response     The Guzzle response object from the request.
     * @param string      $responseBody The actual text-body reply from the server.
     */
    protected function printDebug($method, $url, $postBody, $uploadBytes, $response, $responseBody)
    {
        Debug::printRequest($method, $url);

        // Display the data that was sent via POST, if provided.
        // NOTE: Only provide this from functions that submit meaningful POST data!
        if (!is_null($postBody) && (!is_array($postBody))) {
            Debug::printPostData($postBody);
        }

        // Display the number of bytes uploaded, if provided.
        // NOTE: Only provide this from functions that actually upload files!
        if (!is_null($uploadBytes)) {
            Debug::printUpload(Utils::formatBytes($uploadBytes));
        }

        // Display the number of bytes received from the response, and status code.
        if ($response->hasHeader('x-encoded-content-length')) {
            $bytes = Utils::formatBytes($response->getHeader('x-encoded-content-length')[0]);
        } else {
            $bytes = Utils::formatBytes($response->getHeader('Content-Length')[0]);
        }
        Debug::printHttpCode($response->getStatusCode(), $bytes);

        // Display the actual API response body.
        Debug::printResponse($responseBody, $this->parent->truncatedDebug);
    }

    /**
     * Helper which throws an error if not logged in.
     *
     * Remember to ALWAYS call this function at the top of any API request that
     * requires the user to be logged in!
     */
    protected function throwIfNotLoggedIn()
    {
        // Check the cached login state. May not reflect what will happen on the
        // server. But it's the best we can check without trying the actual request!
        if (!$this->parent->isLoggedIn) {
            throw new InstagramException('User not logged in. Please call login() and then try again.', ErrorCode::INTERNAL_LOGIN_REQUIRED);
        }
    }

    /**
     * Helper which builds in the most important Guzzle options.
     *
     * Takes care of adding all critical options that we need on every request.
     * Such as cookies and the user's proxy. But don't call this function
     * manually. It's automatically called by guzzleRequest()!
     *
     * @param array $options The options specific to the current request.
     *
     * @return array A guzzle options array.
     */
    protected function buildGuzzleOptions(array $options)
    {
        $criticalOptions = [
            'cookies' => ($this->jar instanceof CookieJar ? $this->jar : false),
            'verify'  => $this->verifySSL,
            'proxy'   => (!is_null($this->proxy) ? $this->proxy : null),
        ];

        // Critical options always overwrite identical keys in regular opts.
        // This ensures that we can't screw up the proxy/verify/cookies.
        $finalOptions = array_merge($options, $criticalOptions);

        return $finalOptions;
    }

    /**
     * Wraps Guzzle's request and adds special error handling and options.
     *
     * Automatically throws exceptions on certain very serious HTTP errors.
     * You must ALWAYS use this instead of the raw Guzzle Client! However,
     * you can never assume that its response contains what you wanted. Be sure
     * to validate the API reply too, since Instagram's API calls themselves may
     * fail with a JSON message explaining what went wrong.
     *
     * @param string $method  HTTP method.
     * @param string $uri     URI string.
     * @param array  $options Request options to apply.
     *
     * @throws InstagramException                    with code INTERNAL_API_THROTTLED
     *                                               when throttled by Instagram.
     * @throws \GuzzleHttp\Exception\GuzzleException for any socket related errors.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function guzzleRequest($method, $uri, array $options = [])
    {
        // Add critically important options for authenticating the request.
        $options = $this->buildGuzzleOptions($options);

        // Attempt the request. Will throw in case of socket errors!
        $response = $this->client->request($method, $uri, $options);

        // Detect very serious HTTP status codes in the response.
        $httpCode = $response->getStatusCode();
        switch ($httpCode) {
        case 429: // "429 Too Many Requests"
            throw new InstagramException('Throttled by Instagram because of too many API requests.', ErrorCode::INTERNAL_API_THROTTLED);
            break;
        // NOTE: Detecting "404" errors was intended to help us detect when API
        // endpoints change. But it turns out that A) Instagram uses "valid" 404
        // status codes in actual API replies to indicate "user not found" and
        // similar states for various lookup functions. So we can't die on 404,
        // since "404" API calls actually succeeded in most cases. And B) Their
        // API doesn't 404 if you try an invalid endpoint URL. Instead, it just
        // redirects you to their official homepage. So catching 404 is both
        // pointless and harmful. This is a warning to future contributors!
        // ---
        // case 404: // "404 Not Found"
        //     throw new InstagramException("The requested URL was not found (\"{$uri}\").", ErrorCode::INTERNAL_HTTP_NOTFOUND);
        //     break;
        }

        // Save the new, most up-to-date cookies.
        $this->saveCookieJar();

        // The response may still have serious but "valid response" errors, such
        // as "400 Bad Request". But it's up to the CALLER to handle those!
        return $response;
    }

    /**
     * Perform an Instagram API request.
     */
    public function request($endpoint, $postData = null, $requireLogin = false, $assoc = true)
    {
        if (!$requireLogin) { // Only allow login-requests until logged in.
            $this->throwIfNotLoggedIn();
        }

        // Build request options.
        $headers = [
            'User-Agent'            => $this->userAgent,
            // Keep the API's HTTPS connection alive in Guzzle for future
            // re-use, to greatly speed up all further queries after this.
            'Connection'            => 'keep-alive',
            'Accept'                => '*/*',
            'Accept-Encoding'       => Constants::ACCEPT_ENCODING,
            'X-IG-Capabilities'     => Constants::X_IG_Capabilities,
            'X-IG-Connection-Type'  => Constants::X_IG_Connection_Type,
            'X-IG-Connection-Speed' => mt_rand(1000, 3700).'kbps',
            'X-FB-HTTP-Engine'      => Constants::X_FB_HTTP_Engine,
            'Content-Type'          => Constants::CONTENT_TYPE,
            'Accept-Language'       => Constants::ACCEPT_LANGUAGE,
        ];
        $options = [
            'headers' => $headers,
        ];
        $method = 'GET';
        if ($postData) {
            $method = 'POST';
            $options['body'] = $postData;
        }

        // Perform the API request.
        $response = $this->guzzleRequest($method, Constants::API_URL.$endpoint, $options);

        // Process the response.
        $csrftoken = null;
        $cookies = $this->jar->getIterator();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() == 'csrftoken') {
                $csrftoken = $cookie->getValue();
                break;
            }
        }
        $body = $response->getBody()->getContents();
        $result = self::api_decode($body, $assoc);

        // Debugging.
        if ($this->parent->debug) {
            $this->printDebug($method, $endpoint, $postData, null, $response, $body);
        }

        return [$csrftoken, $result];
    }

    /**
     * Converts a server response to a specific kind of result object.
     *
     * @param mixed $baseClass    An instance of a class object whose properties
     *                          you want to fill from the $response.
     * @param mixed $response     A decoded JSON response from Instagram's server.
     * @param bool  $checkOk      Whether to throw exceptions if the server's
     *                          response wasn't marked as OK by Instagram.
     * @param mixed $fullResponse The raw response object to provide in the
     *                          "getFullResponse()" property. Set this to
     *                          NULL to automatically use $response. That's
     *                          almost always what you want to do!
     *
     * @throws InstagramException
     *
     * @return mixed
     */
    public function getMappedResponseObject($baseClass, $response, $checkOk = true, $fullResponse = null)
    {
        if (is_null($response)) {
            throw new InstagramException('No response from server. Either a connection or configuration error.', ErrorCode::EMPTY_RESPONSE);
        }

        // Perform mapping.
        $mapper = new \JsonMapper();
        $mapper->bStrictNullTypes = false;
        if ($this->parent->apiDeveloperDebug) {
            // API developer debugging? Throws error if class lacks properties.
            $mapper->bExceptionOnUndefinedProperty = true;
        }
        $responseObject = $mapper->map($response, $baseClass);

        // Check if the API response was valid?
        if ($checkOk && !$responseObject->isOk()) {
            throw new InstagramException(get_class($baseClass).': '.$responseObject->getMessage());
        }

        // Save the raw response object as the "getFullResponse()" value.
        if (is_null($fullResponse)) {
            $fullResponse = $response;
        }
        $responseObject->setFullResponse($fullResponse);

        return $responseObject;
    }

    /**
     * Uploads a photo to Instagram.
     *
     * @param string $photoFilename The photo filename.
     * @param null   $upload_id     ? TODO: document this
     * @param bool   $album         Whether this upload will be used in an album.
     * @param null   $customPreview ? TODO: document this
     *
     * @throws InstagramException
     *
     * @return UploadPhotoResponse
     */
    public function uploadPhoto($photoFilename, $upload_id = null, $album = false, $customPreview = null)
    {
        $this->throwIfNotLoggedIn();

        $endpoint = 'upload/photo/';

        // Determine which file to upload.
        if (!is_null($upload_id)) {
            $fileToUpload = Utils::createVideoIcon($photoFilename);
        } elseif ($customPreview) {
            $fileToUpload = file_get_contents($customPreview);
        } else {
            $upload_id = Utils::generateUploadId();
            $fileToUpload = file_get_contents($photoFilename);
        }

        // Prepare payload for the upload request.
        $boundary = $this->parent->uuid;
        //$helper = new AdaptImage(); // <-- WTF? Old leftover code.
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $upload_id,
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $boundary,
            ],
            [
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->parent->token,
            ],
            [
                'type' => 'form-data',
                'name' => 'image_compression',
                'data' => '{"lib_name":"jt","lib_version":"1.3.0","quality":"87"}',
            ],
            [
                'type'     => 'form-data',
                'name'     => 'photo',
                'data'     => $fileToUpload,
                'filename' => 'pending_media_'.Utils::generateUploadId().'.jpg',
                'headers'  => [
                    'Content-Transfer-Encoding: binary',
                    'Content-Type: application/octet-stream',
                ],
            ],
        ];
        if ($album) {
            $bodies[] = [
                'type' => 'form-data',
                'name' => 'is_sidecar',
                'data' => '1',
            ];
        }
        $payload = $this->buildBody($bodies, $boundary);

        // Build the request options.
        $method = 'POST';
        $headers = [
            'User-Agent'            => $this->userAgent,
            'Connection'            => 'keep-alive',
            'Accept'                => '*/*',
            'Accept-Encoding'       => Constants::ACCEPT_ENCODING,
            'X-IG-Capabilities'     => Constants::X_IG_Capabilities,
            'X-IG-Connection-Type'  => Constants::X_IG_Connection_Type,
            'X-IG-Connection-Speed' => mt_rand(1000, 3700).'kbps',
            'X-FB-HTTP-Engine'      => Constants::X_FB_HTTP_Engine,
            'Content-Type'          => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language'       => Constants::ACCEPT_LANGUAGE,
        ];
        $options = [
            'headers' => $headers,
            'body'    => $payload,
        ];

        // Perform the API request.
        $response = $this->guzzleRequest($method, Constants::API_URL.$endpoint, $options);
        $body = $response->getBody()->getContents();

        // Debugging.
        if ($this->parent->debug) {
            $this->printDebug($method, $endpoint, null, strlen($payload), $response, $body);
        }

        return $this->getMappedResponseObject(new UploadPhotoResponse(), self::api_decode($body));
    }

    /**
     * Asks Instagram for parameters for uploading a new video.
     *
     * @param string $upload_id ID to use, or NULL to generate a brand new ID.
     *
     * @return array
     */
    public function requestVideoUploadURL($upload_id = null)
    {
        $this->throwIfNotLoggedIn();

        $endpoint = 'upload/video/';

        // Prepare payload for the "pre-upload" request.
        $boundary = $this->parent->uuid;
        if (is_null($upload_id)) {
            $upload_id = Utils::generateUploadId();
        }
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $upload_id,
            ],
            [
                'type' => 'form-data',
                'name' => '_csrftoken',
                'data' => $this->parent->token,
            ],
            [
                'type' => 'form-data',
                'name' => 'media_type',
                'data' => '2',
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $boundary,
            ],
        ];
        $payload = $this->buildBody($bodies, $boundary);

        // Build the "pre-upload" request's options.
        $method = 'POST';
        $headers = [
            'User-Agent'      => $this->userAgent,
            'Connection'      => 'keep-alive',
            'Accept'          => '*/*',
            'Content-Type'    => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language' => 'en-en',
        ];
        $options = [
            'headers' => $headers,
            'body'    => $payload,
        ];

        // Perform the "pre-upload" API request.
        $response = $this->guzzleRequest($method, Constants::API_URL.$endpoint, $options);

        // Determine where their API wants us to upload the video file.
        $body = $response->getBody()->getContents();
        $result = $this->getMappedResponseObject(new UploadJobVideoResponse(), self::api_decode($body));
        $uploadUrl = $result->getVideoUploadUrls()[3]->url;
        $job = $result->getVideoUploadUrls()[3]->job;

        // Debugging.
        if ($this->parent->debug) {
            $this->printDebug($method, $endpoint, $payload, null, $response, $body);
        }

        return [
            'upload_id'  => $upload_id,
            'upload_url' => $uploadUrl,
            'job'        => $job,
        ];
    }

    /**
     * Performs a chunked upload of a video file.
     *
     * @param string $videoFilename The file to upload.
     * @param array  $uploadParams  Array in requestVideoUploadURL() format.
     *
     * @throws InstagramException if the upload fails.
     *
     * @return UploadVideoResponse
     */
    public function uploadVideoChunks($videoFilename, array $uploadParams)
    {
        $this->throwIfNotLoggedIn();

        // Determine correct file extension for video format.
        $videoExt = pathinfo($videoFilename, PATHINFO_EXTENSION);
        if (strlen($videoExt) == 0) {
            $videoExt = 'mp4'; // Fallback.
        }

        // Video upload must be done in exactly 4 chunks; determine chunk size!
        $numChunks = 4;
        $videoSize = filesize($videoFilename);
        $maxChunkSize = ceil($videoSize / $numChunks);

        // Read and upload each individual chunk.
        $rangeStart = 0;
        $handle = fopen($videoFilename, 'r');
        try {
            for ($chunkIdx = 1; $chunkIdx <= $numChunks; ++$chunkIdx) {
                // Extract the chunk.
                $chunkData = fread($handle, $maxChunkSize);
                $chunkSize = strlen($chunkData);

                // Calculate where the current byte range will end.
                // NOTE: Range is 0-indexed, and Start is the first byte of the
                // new chunk we're uploading, hence we MUST subtract 1 from End.
                // And our FINAL chunk's End must be 1 less than the filesize!
                $rangeEnd = $rangeStart + ($chunkSize - 1);

                // Build the current chunk's request options.
                $method = 'POST';
                $headers = [
                    'User-Agent'          => $this->userAgent,
                    'Connection'          => 'keep-alive',
                    'Accept'              => '*/*',
                    'Cookie2'             => '$Version=1',
                    'Accept-Encoding'     => 'gzip, deflate',
                    'Content-Type'        => 'application/octet-stream',
                    'Session-ID'          => $uploadParams['upload_id'],
                    'Accept-Language'     => 'en-en',
                    'Content-Disposition' => "attachment; filename=\"video.{$videoExt}\"",
                    'Content-Range'       => 'bytes '.$rangeStart.'-'.$rangeEnd.'/'.$videoSize,
                    'job'                 => $uploadParams['job'],
                ];
                $options = [
                    'headers' => $headers,
                    'body'    => $chunkData,
                ];

                // Perform the upload of the current chunk.
                $response = $this->guzzleRequest($method, $uploadParams['upload_url'], $options);
                $body = $response->getBody()->getContents();

                // Debugging.
                if ($this->parent->debug) {
                    $this->printDebug($method, $uploadParams['upload_url'], null, $chunkSize, $response, $body);
                }

                // Check if Instagram's server has bugged out.
                // NOTE: On everything except the final chunk, they MUST respond
                // with "0-BYTESTHEYHAVESOFAR/TOTALBYTESTHEYEXPECT". The "0-" is
                // what matters. When they bug out, they drop chunks and the
                // start range on the server-side won't be at zero anymore.
                if ($chunkIdx != $numChunks) {
                    if (strncmp($body, '0-', 2) !== 0) {
                        // Their range doesn't start with "0-". Abort!
                        break; // Don't waste time uploading further chunks!
                    }
                }

                // Update the range's Start for the next iteration.
                // NOTE: It's the End-byte of the previous range, plus one.
                $rangeStart = $rangeEnd + 1;
            }
        } finally {
            // Guaranteed to release handle even if something bad happens above!
            fclose($handle);
        }

        // NOTE: $response and $body below refer to the final chunk's result!

        // Protection against Instagram's upload server being bugged out!
        // NOTE: When their server is bugging out, the final chunk result will
        // just be yet another range specifier such as "328600-657199/657200",
        // instead of a "{...}" JSON object. Because their server will have
        // dropped all earlier chunks when they bug out (due to overload or w/e).
        if (substr($body, 0, 1) !== '{') {
            throw new InstagramException("Upload of \"{$videoFilename}\" failed. Instagram's server returned an unexpected reply.", ErrorCode::INTERNAL_UPLOAD_FAILED);
        }

        // Verify that the chunked upload was successful.
        $upload = $this->getMappedResponseObject(new UploadVideoResponse(), self::api_decode($body));
        if (!is_null($upload->getMessage())) {
            throw new InstagramException($upload->getMessage());
        }

        return $upload;
    }

    /**
     * Uploads a video to Instagram.
     *
     * @param string $videoFilename The video filename.
     * @param string $caption       Caption to use for the video.
     * @param bool   $story         Whether this upload will be used in a story.
     * @param null   $reel_mentions ? TODO: document this
     * @param string $customPreview Optional path to custom video thumbnail.
     *                              If nothing provided, we generate from video.
     * @param int    $maxAttempts   Total attempts to upload all chunks before throwing.
     *
     * @throws InstagramException
     *
     * @return ConfigureVideoResponse
     */
    public function uploadVideo($videoFilename, $caption = null, $story = false, $reel_mentions = null, $customPreview = null, $maxAttempts = 4)
    {
        $this->throwIfNotLoggedIn();

        $endpoint = 'upload/video/';

        // Request parameters for uploading a new video.
        $uploadParams = $this->requestVideoUploadURL();

        // Upload the entire video file, with retries in case of chunk upload errors.
        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            try {
                $upload = $this->uploadVideoChunks($videoFilename, $uploadParams);
                break;
            } catch (InstagramException $e) {
                if ($attempt < $maxAttempts && $e->getCode() == ErrorCode::INTERNAL_UPLOAD_FAILED) {
                    // Do nothing, since we'll be retrying the failed upload...
                } else {
                    // Re-throw all unhandled exceptions.
                    throw $e;
                }
            }
        }

        // Configure the uploaded video and attach it to our timeline.
        for ($attempt = 1; $attempt <= 4; ++$attempt) {
            $configure = $this->parent->configureVideo($uploadParams['upload_id'], $videoFilename, $caption, $story, $customPreview);
            //$this->parent->expose();
            if ($configure->getMessage() != 'Transcode timeout') {
                break; // Success. Exit loop.
            } elseif ($attempt < 4) {
                sleep(1); // Wait a little before the next retry.
            }
        }

        return $configure;
    }

    /**
     * Change your profile picture.
     *
     * @param string $photoFilename The path to a photo file.
     *
     * @throws InstagramException
     *
     * @return User
     */
    public function changeProfilePicture($photoFilename)
    {
        $this->throwIfNotLoggedIn();

        $endpoint = 'accounts/change_profile_picture/';

        if (is_null($photoFilename)) {
            throw new InstagramException('No photo path provided.', ErrorCode::INTERNAL_INVALID_ARGUMENT);
        }

        // Prepare payload for the upload request.
        $boundary = $this->parent->uuid;
        $uData = json_encode([
            '_csrftoken' => $this->parent->token,
            '_uuid'      => $boundary,
            '_uid'       => $this->parent->username_id,
        ]);
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'ig_sig_key_version',
                'data' => Constants::SIG_KEY_VERSION,
            ],
            [
                'type' => 'form-data',
                'name' => 'signed_body',
                'data' => hash_hmac('sha256', $uData, Constants::IG_SIG_KEY).$uData,
            ],
            [
                'type'     => 'form-data',
                'name'     => 'profile_pic',
                'data'     => file_get_contents($photoFilename),
                'filename' => 'profile_pic',
                'headers'  => [
                    'Content-Type: application/octet-stream',
                    'Content-Transfer-Encoding: binary',
                ],
            ],
        ];
        $payload = $this->buildBody($bodies, $boundary);

        // Build the request options.
        $method = 'POST';
        $headers = [
            'User-Agent'       => $this->userAgent,
            'Proxy-Connection' => 'keep-alive',
            'Connection'       => 'keep-alive',
            'Accept'           => '*/*',
            'Content-Type'     => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language'  => 'en-en',
        ];
        $options = [
            'headers' => $headers,
            'body'    => $payload,
        ];

        // Perform the API request.
        $response = $this->guzzleRequest($method, Constants::API_URL.$endpoint, $options);
        $body = $response->getBody()->getContents();

        // Debugging.
        if ($this->parent->debug) {
            $this->printDebug($method, $endpoint, null, strlen($payload), $response, $body);
        }

        return $this->getMappedResponseObject(new User(), self::api_decode($body));
    }

    /**
     * Perform a direct media share to specific users.
     *
     * @param string          $shareType  Either "share", "message" or "photo".
     * @param string[]|string $recipients Either a single recipient or an array
     *                                    of multiple recipient strings.
     * @param array           $shareData  Depends on shareType: "share" uses
     *                                    "text" and "media_id". "message" uses
     *                                    "text". "photo" uses "text" and "filepath".
     *
     * @throws InstagramException
     *
     * @return Response
     */
    public function directShare($shareType, $recipients, array $shareData)
    {
        $this->throwIfNotLoggedIn();

        // Determine which endpoint to use and validate input.
        switch ($shareType) {
        case 'share':
            $endpoint = 'direct_v2/threads/broadcast/media_share/?media_type=photo';
            if ((!isset($shareData['text']) || is_null($shareData['text']))
                && (!isset($shareData['media_id']) || is_null($shareData['media_id']))) {
                throw new InstagramException('You must provide either a text message or a media id.', ErrorCode::INTERNAL_INVALID_ARGUMENT);
            }
            break;
        case 'message':
            $endpoint = 'direct_v2/threads/broadcast/text/';
            if (!isset($shareData['text']) || is_null($shareData['text'])) {
                throw new InstagramException('No text message provided.', ErrorCode::INTERNAL_INVALID_ARGUMENT);
            }
            break;
        case 'photo':
            $endpoint = 'direct_v2/threads/broadcast/upload_photo/';
            if (!isset($shareData['filepath']) || is_null($shareData['filepath'])) {
                throw new InstagramException('No photo path provided.', ErrorCode::INTERNAL_INVALID_ARGUMENT);
            }
            break;
        }

        // Build the list of direct-share recipients.
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }
        $recipient_users = '"'.implode('","', $recipients).'"';

        // Prepare payload for the direct-share request.
        // WARNING: EDIT THIS *VERY CAREFULLY* IN THE FUTURE!
        // THE DIRECT-SHARE REQUESTS USE A LOT OF IDENTICAL DATA,
        // SO WE CONSTRUCT THEIR FINAL $bodies STEP BY STEP TO AVOID
        // CODE REPETITION. BUT RECKLESS FUTURE CHANGES BELOW COULD
        // BREAK *ALL* REQUESTS IF YOU ARE NOT *VERY* CAREFUL!!!
        $boundary = $this->parent->uuid;
        $bodies = [];
        if ($shareType == 'share') {
            $bodies[] = [
                'type' => 'form-data',
                'name' => 'media_id',
                'data' => $shareData['media_id'],
            ];
        }
        $bodies[] = [
            'type' => 'form-data',
            'name' => 'recipient_users',
            'data' => "[[{$recipient_users}]]",
        ];
        $bodies[] = [
            'type' => 'form-data',
            'name' => 'client_context',
            'data' => $boundary,
        ];
        $bodies[] = [
            'type' => 'form-data',
            'name' => 'thread_ids',
            'data' => '["0"]',
        ];
        if ($shareType == 'photo') {
            $bodies[] = [
                'type'     => 'form-data',
                'name'     => 'photo',
                'data'     => file_get_contents($shareData['filepath']),
                'filename' => 'photo',
                'headers'  => [
                    'Content-Type: '.mime_content_type($shareData['filepath']),
                    'Content-Transfer-Encoding: binary',
                ],
            ];
        }
        $bodies[] = [
            'type' => 'form-data',
            'name' => 'text',
            'data' => (!isset($shareData['text']) || is_null($shareData['text']) ? '' : $shareData['text']),
        ];
        $payload = $this->buildBody($bodies, $boundary);

        // Build the request options.
        $method = 'POST';
        $headers = [
            'User-Agent'       => $this->userAgent,
            'Proxy-Connection' => 'keep-alive',
            'Connection'       => 'keep-alive',
            'Accept'           => '*/*',
            'Content-Type'     => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language'  => 'en-en',
        ];
        $options = [
            'headers' => $headers,
            'body'    => $payload,
        ];

        // Perform the API request.
        $response = $this->guzzleRequest($method, Constants::API_URL.$endpoint, $options);
        $body = $response->getBody()->getContents();

        // Debugging.
        if ($this->parent->debug) {
            $this->printDebug($method, $endpoint, null, strlen($payload), $response, $body);
        }

        // Verify that the direct-share upload was successful.
        $upload = $this->getMappedResponseObject(new Response(), self::api_decode($body));
        if (!is_null($upload->getMessage())) {
            throw new InstagramException($upload->getMessage());
        }

        return $upload;
    }

    /**
     * Internal helper for building a proper request body.
     *
     * @param array  $bodies
     * @param string $boundary
     *
     * @return string
     */
    protected function buildBody(array $bodies, $boundary)
    {
        $body = '';
        foreach ($bodies as $b) {
            $body .= '--'.$boundary."\r\n";
            $body .= 'Content-Disposition: '.$b['type'].'; name="'.$b['name'].'"';
            if (isset($b['filename'])) {
                $ext = pathinfo($b['filename'], PATHINFO_EXTENSION);
                $body .= '; filename="'.'pending_media_'.Utils::generateUploadId().'.'.$ext.'"';
            }
            if (isset($b['headers']) && is_array($b['headers'])) {
                foreach ($b['headers'] as $header) {
                    $body .= "\r\n".$header;
                }
            }

            $body .= "\r\n\r\n".$b['data']."\r\n";
        }
        $body .= '--'.$boundary.'--';

        return $body;
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
     *                         or NULL if unable to decode JSON.
     */
    public static function api_decode($json, $assoc = false)
    {
        return json_decode($json, $assoc, 512, JSON_BIGINT_AS_STRING);
    }
}
