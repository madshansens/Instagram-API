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
            // Tells Guzzle to throw exceptions on non-"200 OK" replies!
            'http_errors'     => true,
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
     * Wraps Guzzle's request and adds special exception handling.
     *
     * You must ALWAYS use this instead of the raw Guzzle Client!
     *
     * @param string $method  HTTP method.
     * @param string $uri     URI string.
     * @param array  $options Request options to apply.
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws InstagramException with code INTERNAL_API_THROTTLED when throttled.
     * @throws \GuzzleHttp\Exception\GuzzleException for any HTTP related errors.
     */
    protected function guzzleRequest($method, $uri, array $options = [])
    {
        try {
            return $this->client->request($method, $uri, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            // Detect Instagram API throttling.
            $response = $e->getResponse();
            if (is_object($response)) {
                $httpCode = $response->getStatusCode();
                if ($httpCode == 429) { // "429 Too Many Requests"
                    throw new InstagramException('Throttled by Instagram because of too many API requests.', ErrorCode::INTERNAL_API_THROTTLED);
                }
            }

            // Simply re-throw the original exception in all other cases.
            // This will be serious errors such as 404, socket errors, etc!
            throw $e;
        }
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
            'cookies' => ($this->jar instanceof CookieJar ? $this->jar : false),
            'headers' => $headers,
            'verify'  => $this->verifySSL,
        ];
        $method = 'GET';
        if ($postData) {
            $method = 'POST';
            $options['body'] = $postData;
        }
        if (!is_null($this->proxy)) {
            $options['proxy'] = $this->proxy;
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
        $result = json_decode($body, $assoc, 512, JSON_BIGINT_AS_STRING);

        // Debugging.
        if ($this->parent->debug) {
            $this->printDebug($method, $endpoint, $postData, null, $response, $body);
        }

        // Save current cookies.
        $this->saveCookieJar();

        return [$csrftoken, $result];
    }

    public function getResponseWithResult($obj, $response)
    {
        if (is_null($response)) {
            throw new InstagramException('No response from server, connection or configure error', ErrorCode::EMPTY_RESPONSE);
        }

        $mapper = new \JsonMapper();

        $mapper->bStrictNullTypes = false;
        if (isset($_GET['debug'])) {
            $mapper->bExceptionOnUndefinedProperty = true;
        }

        $responseObject = $mapper->map($response, $obj);

        if (!$responseObject->isOk()) {
            throw new InstagramException(get_class($obj).' : '.$responseObject->getMessage());
        }
        $responseObject->setFullResponse($response);

        return $responseObject;
    }

    /**
     * @param $photo
     * @param null $caption
     * @param null $upload_id
     * @param null $customPreview
     * @param null $location
     * @param null $filter
     * @param bool $reel_flag
     *
     * @throws InstagramException
     */
    public function uploadPhoto($photo, $upload_id = null, $album = false)
    {
        $endpoint = 'upload/photo/';
        $boundary = $this->parent->uuid;
        //$helper = new AdaptImage();

        if (!is_null($upload_id)) {
            $fileToUpload = Utils::createVideoIcon($photo);
        } else {
            $upload_id = Utils::generateUploadId();
            $fileToUpload = file_get_contents($photo);
        }

        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'upload_id',
                'data' => $upload_id,
            ],
            [
                'type' => 'form-data',
                'name' => '_uuid',
                'data' => $this->parent->uuid,
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

        $data = $this->buildBody($bodies, $boundary);

        $headers = [
            'User-Agent'            => $this->userAgent,
            'Connection'            => 'close',
            'Accept'                => '*/*',
            'Accept-Encoding'       => Constants::ACCEPT_ENCODING,
            'X-IG-Capabilities'     => Constants::X_IG_Capabilities,
            'X-IG-Connection-Type'  => Constants::X_IG_Connection_Type,
            'X-IG-Connection-Speed' => mt_rand(1000, 3700).'kbps',
            'X-FB-HTTP-Engine'      => Constants::X_FB_HTTP_Engine,
            'Content-Type'          => Constants::CONTENT_TYPE,
            'Accept-Language'       => Constants::ACCEPT_LANGUAGE,
            'Content-Length'        => strlen($data),
            'Content-Type'          => 'multipart/form-data; boundary='.$boundary,
        ];

        if ($this->parent->settingsAdapter['type'] == 'file') {
            $cookieJar = new FileCookieJar($this->parent->settings->cookiesPath);
        } else {
            $cookieJar = new FileCookieJar(tempnam(sys_get_temp_dir(), uniqid('_instagram_cookie')));
        }

        $options = [
            'cookies' => $cookieJar,
            'body'    => $data,
            'headers' => $headers,
        ];

        if ($this->proxy) {
            // TODO: rewrite to properly read proxy just like in request()
        }

        $response = $this->guzzleRequest('POST', Constants::API_URL.$endpoint, $options);

        $cookies = $cookieJar->getIterator();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() == 'csrftoken') {
                $csrftoken = $cookie->getValue();
            }
        }
        $header = $csrftoken;
        $body = json_decode($response->getBody()->getContents());
        $httpCode = $response->getStatusCode();

        $upload = $this->getResponseWithResult(new UploadPhotoResponse(), $body);

        if ($this->parent->debug) {
            Debug::printRequest('POST', $endpoint);

            $uploadBytes = Utils::formatBytes(strlen($data));
            Debug::printUpload($uploadBytes);

            if ($response->hasHeader('x-encoded-content-length')) {
                $bytes = Utils::formatBytes($response->getHeader('x-encoded-content-length')[0]);
            } else {
                $bytes = Utils::formatBytes($response->getHeader('Content-Length')[0]);
            }
            Debug::printHttpCode($httpCode, $bytes);
            Debug::printResponse(json_encode($body));
        }

        if ($this->parent->settingsAdapter['type'] == 'mysql') {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        } elseif ($this->parent->settings->setting instanceof SettingsAdapter\SettingsInterface) {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        }

        return $upload;
    }

    /**
     * Uploads a video to Instagram.
     *
     * @param string $video         The video filename.
     * @param string $caption       Caption to use for the video.
     * @param string $customPreview Optional path to custom video thumbnail.
     *                              If nothing provided, we generate from video.
     *
     * @throws InstagramException
     */
    public function uploadVideo($video, $caption = null, $customPreview = null)
    {
        $this->throwIfNotLoggedIn();

        $endpoint = 'upload/video/';

        // Prepare payload for the "pre-upload" request.
        $boundary = $this->parent->uuid;
        $upload_id = Utils::generateUploadId();
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
                'data' => $this->parent->uuid,
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
            'cookies' => ($this->jar instanceof CookieJar ? $this->jar : false),
            'headers' => $headers,
            'verify'  => $this->verifySSL,
            'body'    => $payload,
        ];
        if (!is_null($this->proxy)) {
            $options['proxy'] = $this->proxy;
        }

        // Perform the "pre-upload" API request.
        $response = $this->guzzleRequest($method, Constants::API_URL.$endpoint, $options);

        // Determine where their API wants us to upload the video file.
        $body = $response->getBody()->getContents();
        $result = $this->getResponseWithResult(new UploadJobVideoResponse(), json_decode($body));
        $uploadUrl = $result->getVideoUploadUrls()[3]->url;
        $job = $result->getVideoUploadUrls()[3]->job;

        // Debugging.
        if ($this->parent->debug) {
            $this->printDebug($method, $endpoint, $payload, null, $response, $body);
        }

        // Video upload must be done in exactly 4 chunks; determine chunk size!
        $numChunks = 4;
        $videoSize = filesize($video);
        $maxChunkSize = ceil($videoSize / $numChunks);

        // Read and upload each individual chunk.
        $rangeStart = 0;
        $handle = fopen($video, 'r');
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
                    'Session-ID'          => $upload_id,
                    'Accept-Language'     => 'en-en',
                    'Content-Disposition' => 'attachment; filename="video.mov"',
                    'Content-Range'       => 'bytes '.$rangeStart.'-'.$rangeEnd.'/'.$videoSize,
                    'job'                 => $job,
                ];
                $options = [
                    'cookies' => ($this->jar instanceof CookieJar ? $this->jar : false),
                    'headers' => $headers,
                    'verify'  => $this->verifySSL,
                    'body'    => $chunkData,
                ];
                if (!is_null($this->proxy)) {
                    $options['proxy'] = $this->proxy;
                }

                // Perform the upload of the current chunk.
                $response = $this->guzzleRequest($method, $uploadUrl, $options);
                $body = $response->getBody()->getContents();

                // Debugging.
                if ($this->parent->debug) {
                    $this->printDebug($method, $uploadUrl, null, $chunkSize, $response, $body);
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

        // Verify that the chunked upload was successful.
        $upload = $this->getResponseWithResult(new UploadVideoResponse(), json_decode($body));
        if (!is_null($upload->getMessage())) {
            throw new InstagramException($upload->getMessage()."\n");
        }

        // Configure the uploaded video and attach it to our timeline.
        // TODO: Rewrite this old, unfinished code! It causes "Call to undefined method InstagramAPI\Instagram::configureToReel()"!
        $configure = $this->parent->configureVideo($upload_id, $video, $caption, $customPreview);
        //$this->parent->expose();
        $attempts = 0;
        while ($configure->getMessage() == 'Transcode timeout' && $attempts < 3) {
            sleep(1);
            $configure = $this->parent->configureVideo($upload_id, $video, $caption, $customPreview);
            $attempts++;
        }

        // Save current cookies.
        $this->saveCookieJar();

        return $configure;
    }

    public function changeProfilePicture($photo)
    {
        if (is_null($photo)) {
            echo "Photo not valid\n\n";

            return;
        }

        $uData = json_encode([
            '_csrftoken' => $this->parent->token,
            '_uuid'      => $this->parent->uuid,
            '_uid'       => $this->parent->username_id,
        ]);

        $endpoint = 'accounts/change_profile_picture/';
        $boundary = $this->parent->uuid;
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
                'data'     => file_get_contents($photo),
                'filename' => 'profile_pic',
                'headers'  => [
                    'Content-Type: application/octet-stream',
                    'Content-Transfer-Encoding: binary',
                ],
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Proxy-Connection: keep-alive',
            'Connection: keep-alive',
            'Accept: */*',
            'Content-Type: multipart/form-data; boundary='.$boundary,
            'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Constants::API_URL.$endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($this->parent->settingsAdapter['type'] == 'file') {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->settings->cookiesPath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->settings->cookiesPath);
        } else {
            $cookieJar = $this->parent->settings->get('cookies');
            $cookieJarFile = tempnam(sys_get_temp_dir(), uniqid('_instagram_cookie'));

            file_put_contents($cookieJarFile, $cookieJar);

            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJarFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJarFile);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->proxy) {
            // TODO: rewrite to properly read proxy just like in request()
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        if ($this->parent->debug) {
            Debug::printRequest('POST', $endpoint);

            $uploadBytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_UPLOAD));
            Debug::printUpload($uploadBytes);

            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            Debug::printHttpCode($httpCode, $bytes);
            Debug::printResponse(substr($resp, $header_len));
        }

        curl_close($ch);
        if ($this->parent->settingsAdapter['type'] == 'mysql') {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        } elseif ($this->parent->settings->setting instanceof SettingsAdapter\SettingsInterface) {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        }
    }

    public function direct_share($media_id, $recipients, $text = null)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $string = [];
        foreach ($recipients as $recipient) {
            $string[] = "\"$recipient\"";
        }

        $recipient_users = implode(',', $string);

        $endpoint = 'direct_v2/threads/broadcast/media_share/?media_type=photo';
        $boundary = $this->parent->uuid;
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'media_id',
                'data' => $media_id,
            ],
            [
                'type' => 'form-data',
                'name' => 'recipient_users',
                'data' => "[[$recipient_users]]",
            ],
            [
                'type' => 'form-data',
                'name' => 'client_context',
                'data' => $this->parent->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => 'thread_ids',
                'data' => '["0"]',
            ],
            [
                'type' => 'form-data',
                'name' => 'text',
                'data' => is_null($text) ? '' : $text,
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Proxy-Connection: keep-alive',
            'Connection: keep-alive',
            'Accept: */*',
            'Content-Type: multipart/form-data; boundary='.$boundary,
            'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Constants::API_URL.$endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($this->parent->settingsAdapter['type'] == 'file') {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->settings->cookiesPath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->settings->cookiesPath);
        } else {
            $cookieJar = $this->parent->settings->get('cookies');
            $cookieJarFile = tempnam(sys_get_temp_dir(), uniqid('_instagram_cookie'));

            file_put_contents($cookieJarFile, $cookieJar);

            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJarFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJarFile);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->proxy) {
            // TODO: rewrite to properly read proxy just like in request()
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = json_decode(substr($resp, $header_len), true);

        if ($this->parent->debug) {
            Debug::printRequest('POST', $endpoint);

            $uploadBytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_UPLOAD));
            Debug::printUpload($uploadBytes);

            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            Debug::printHttpCode($httpCode, $bytes);
            Debug::printResponse(substr($resp, $header_len));
        }

        curl_close($ch);
        if ($this->parent->settingsAdapter['type'] == 'mysql') {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        } elseif ($this->parent->settings->setting instanceof SettingsAdapter\SettingsInterface) {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        }
    }

    public function direct_message($recipients, $text)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $string = [];
        foreach ($recipients as $recipient) {
            $string[] = "\"$recipient\"";
        }

        $recipient_users = implode(',', $string);

        $endpoint = 'direct_v2/threads/broadcast/text/';
        $boundary = $this->parent->uuid;
        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'recipient_users',
                'data' => "[[$recipient_users]]",
            ],
            [
                'type' => 'form-data',
                'name' => 'client_context',
                'data' => $this->parent->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => 'thread_ids',
                'data' => '["0"]',
            ],
            [
                'type' => 'form-data',
                'name' => 'text',
                'data' => is_null($text) ? '' : $text,
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Proxy-Connection: keep-alive',
            'Connection: keep-alive',
            'Accept: */*',
            'Content-Type: multipart/form-data; boundary='.$boundary,
            'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Constants::API_URL.$endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($this->parent->settingsAdapter['type'] == 'file') {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->settings->cookiesPath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->settings->cookiesPath);
        } else {
            $cookieJar = $this->parent->settings->get('cookies');
            $cookieJarFile = tempnam(sys_get_temp_dir(), uniqid('_instagram_cookie'));

            file_put_contents($cookieJarFile, $cookieJar);

            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJarFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJarFile);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->proxy) {
            // TODO: rewrite to properly read proxy just like in request()
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = $this->getResponseWithResult(new Response(), json_decode(substr($resp, $header_len)));

        if (!$upload->isOk()) {
            throw new InstagramException($upload->getMessage());
            return;
        }

        if ($this->parent->debug) {
            Debug::printRequest('POST', $endpoint);

            $uploadBytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_UPLOAD));
            Debug::printUpload($uploadBytes);

            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            Debug::printHttpCode($httpCode, $bytes);
            Debug::printResponse(substr($resp, $header_len));
        }

        curl_close($ch);
        if ($this->parent->settingsAdapter['type'] == 'mysql') {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        } elseif ($this->parent->settings->setting instanceof SettingsAdapter\SettingsInterface) {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        }
    }

    public function direct_photo($recipients, $filepath, $text)
    {
        if (!is_array($recipients)) {
            $recipients = [$recipients];
        }

        $string = [];
        foreach ($recipients as $recipient) {
            $string[] = "\"$recipient\"";
        }

        $recipient_users = implode(',', $string);

        $endpoint = 'direct_v2/threads/broadcast/upload_photo/';
        $boundary = $this->parent->uuid;
        $photo = file_get_contents($filepath);

        $bodies = [
            [
                'type' => 'form-data',
                'name' => 'recipient_users',
                'data' => "[[$recipient_users]]",
            ],
            [
                'type' => 'form-data',
                'name' => 'client_context',
                'data' => $this->parent->uuid,
            ],
            [
                'type' => 'form-data',
                'name' => 'thread_ids',
                'data' => '["0"]',
            ],
            [
                'type'     => 'form-data',
                'name'     => 'photo',
                'data'     => $photo,
                'filename' => 'photo',
                'headers'  => [
                    'Content-Type: '.mime_content_type($filepath),
                    'Content-Transfer-Encoding: binary',
                ],
            ],
            [
                'type' => 'form-data',
                'name' => 'text',
                'data' => is_null($text) ? '' : $text,
            ],
        ];

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Proxy-Connection: keep-alive',
            'Connection: keep-alive',
            'Accept: */*',
            'Content-Type: multipart/form-data; boundary='.$boundary,
            'Content-Length: '.strlen($data),
            'Connection: keep-alive',
            'Accept-Language: en-en',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, Constants::API_URL.$endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if ($this->parent->settingsAdapter['type'] == 'file') {
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->parent->settings->cookiesPath);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->parent->settings->cookiesPath);
        } else {
            $cookieJar = $this->parent->settings->get('cookies');
            $cookieJarFile = tempnam(sys_get_temp_dir(), uniqid('_instagram_cookie'));

            file_put_contents($cookieJarFile, $cookieJar);

            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJarFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieJarFile);
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        if ($this->proxy) {
            // TODO: rewrite to properly read proxy just like in request()
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $upload = $this->getResponseWithResult(new Response(), json_decode(substr($resp, $header_len)));

        if (!$upload->isOk()) {
            throw new InstagramException($upload->getMessage());
        }

        if ($this->parent->debug) {
            Debug::printRequest('POST', $endpoint);

            $uploadBytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_UPLOAD));
            Debug::printUpload($uploadBytes);

            $bytes = Utils::formatBytes(curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD));
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            Debug::printHttpCode($httpCode, $bytes);
            Debug::printResponse(substr($resp, $header_len));
        }

        curl_close($ch);
        if ($this->parent->settingsAdapter['type'] == 'mysql') {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        } elseif ($this->parent->settings->setting instanceof SettingsAdapter\SettingsInterface) {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        }
    }

    protected function buildBody($bodies, $boundary)
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
}
