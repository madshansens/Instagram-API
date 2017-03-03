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
            // TODO: Consider whether we should throw exceptions on non-200 OK replies:
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

        // TODO: Perhaps force login via $this->parent->login(true) here or somewhere
        // else, if we don't have any session. Such as if we couldn't load the
        // session cookies from disk/decode them from memory above.
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
     * Perform an Instagram API request.
     */
    public function request($endpoint, $post = null, $login = false, $flood_wait = false, $assoc = true)
    {
        if (!$this->parent->isLoggedIn && !$login) {
            throw new InstagramException("User is not logged in - login() must be called before making login-enforced requests.\n", ErrorCode::INTERNAL_LOGIN_REQUIRED);
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
        if ($post) {
            $method = 'POST';
            $options['body'] = $post;
        }
        if (!is_null($this->proxy)) {
            $options['proxy'] = $this->proxy;
        }

        // Perform the API request.
        $response = $this->client->request($method, Constants::API_URL.$endpoint, $options);

        // TODO: Check HTTP status code here before trying to use the response.
        // But preferably, we should enable http_errors in Guzzle so that the
        // request above throws exceptions instead. And then use try{} catch{}
        // in our API and in any user code, to precisely control retry-behavior,
        // and to avoid muddying return values by including errors in return values.
        $httpCode = $response->getStatusCode();

        // Process the response.
        $csrftoken = null;
        $cookies = $this->jar->getIterator();
        foreach ($cookies as $cookie) {
            if ($cookie->getName() == 'csrftoken') {
                $csrftoken = $cookie->getValue();
                break;
            }
        }
        $body = json_decode($response->getBody()->getContents());

        // Debugging.
        if ($this->parent->debug) {
            Debug::printRequest($method, $endpoint);
            if (!is_null($post) && (!is_array($post))) {
                Debug::printPostData($post);
            }

            if ($response->hasHeader('x-encoded-content-length')) {
                $bytes = Utils::formatBytes($response->getHeader('x-encoded-content-length')[0]);
            } else {
                $bytes = Utils::formatBytes($response->getHeader('Content-Length')[0]);
            }

            Debug::printHttpCode($httpCode, $bytes);
            Debug::printResponse(json_encode($body), $this->parent->truncatedDebug);
        }

        // Tell any custom settings adapters to persist the current cookies.
        if ($this->parent->settingsAdapter['type'] == 'mysql'
            || $this->parent->settings->setting instanceof SettingsAdapter\SettingsInterface) {
            $newCookies = $this->getCookieJarAsJSON();
            $this->parent->settings->set('cookies', $newCookies);
        }

        // TODO: Make this cleaner... It's far better and cleaner to let the
        // caller handle API retries instead, via exceptions and try{} catch{}
        // instead of this hardcoded blob. ;-)
        if ($httpCode == 429 && $flood_wait) {
            if ($this->parent->debug) {
                echo "Too many requests! Sleeping 2s\n";
            }
            sleep(2);

            return $this->request($endpoint, $post, $login, false, $assoc);
        } else {
            return [$csrftoken, $body];
        }
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

        $response = $this->client->request('POST', Constants::API_URL.$endpoint, $options);

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

    public function uploadVideo($video, $caption = null, $customPreview = null)
    {
        $videoData = file_get_contents($video);

        $endpoint = 'upload/video/';
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

        $data = $this->buildBody($bodies, $boundary);
        $headers = [
            'Connection' => 'keep-alive',
            'Accept' => '*/*',
            'Host' => 'i.instagram.com',
            'Content-Type' => 'multipart/form-data; boundary='.$boundary,
            'Accept-Language' => 'en-en',
            'User-Agent'            => $this->userAgent,
        ];

        $options = [
            'cookies' => ($this->jar instanceof CookieJar ? $this->jar : false),
            'headers' => $headers,
            'verify'  => $this->verifySSL,
            'body'    => $data,
        ];

        if (!is_null($this->proxy)) {
            $options['proxy'] = $this->proxy;
        }

        // Perform the API request.
        $response = $this->client->request('POST', Constants::API_URL.$endpoint, $options);
        $json = $response->getBody()->getContents();

        $body = $this->getResponseWithResult(new UploadJobVideoResponse(), json_decode($json));
        $uploadUrl = $body->getVideoUploadUrls()[3]->url;
        $job = $body->getVideoUploadUrls()[3]->job;

        $request_size = floor(strlen($videoData) / 4);
        $lastRequestExtra = (strlen($videoData) - ($request_size * 4));

        if ($this->parent->debug) {
            Debug::printRequest('POST', $endpoint);

            $uploadBytes = Utils::formatBytes(strlen($data));
            Debug::printUpload($uploadBytes);

            if ($response->hasHeader('x-encoded-content-length')) {
                $bytes = Utils::formatBytes($response->getHeader('x-encoded-content-length')[0]);
            } else {
                $bytes = Utils::formatBytes($response->getHeader('Content-Length')[0]);
            }
            $httpCode = $response->getStatusCode();
            Debug::printHttpCode($httpCode, $bytes);
            Debug::printResponse($json, $this->parent->truncatedDebug);
        }

        for ($a = 0; $a <= 3; ++$a) {
            $start = ($a * $request_size);
            $end = ($a + 1) * $request_size + ($a == 3 ? $lastRequestExtra : 0);

            $headers = [
                'User-Agent'            => $this->userAgent,
                'Connection' => 'keep-alive',
                'Accept' => '*/*',
                'Host' => 'upload.instagram.com',
                'Cookie2' => '$Version=1',
                'Accept-Encoding' => 'gzip, deflate',
                'Content-Type' => 'application/octet-stream',
                'Session-ID' => $upload_id,
                'Accept-Language' => 'en-en',
                'Content-Disposition' => 'attachment; filename="video.mov"',
                'Content-Length' => ($end - $start),
                'Content-Range' => 'bytes '.$start.'-'.($end - 1).'/'.strlen($videoData),
                'job' => $job,
            ];

            $options = [
                'cookies' => ($this->jar instanceof CookieJar ? $this->jar : false),
                'headers' => $headers,
                'verify'  => $this->verifySSL,
                'body'    => substr($videoData, $start, $end),
                'debug'   => true
            ];

            if (!is_null($this->proxy)) {
                $options['proxy'] = $this->proxy;
            }

            // Perform the API request.
            $response = $this->client->request('POST', $uploadUrl, $options);
            $body = $response->getBody()->getContents();

            if ($this->parent->debug) {
                Debug::printRequest('POST', $uploadUrl);

                $uploadBytes = Utils::formatBytes(strlen(substr($videoData, $start, $end)));
                Debug::printUpload($uploadBytes);

                if ($response->hasHeader('x-encoded-content-length')) {
                    $bytes = Utils::formatBytes($response->getHeader('x-encoded-content-length')[0]);
                } else {
                    $bytes = Utils::formatBytes($response->getHeader('Content-Length')[0]);
                }
                $httpCode = $response->getStatusCode();
                Debug::printHttpCode($httpCode, $bytes);
                Debug::printResponse($body, $this->parent->truncatedDebug);
            }
        }
        $response = $this->client->request('POST', $uploadUrl, $options);
        $body = $response->getBody()->getContents();


        /*
        $upload = $this->getResponseWithResult(new UploadVideoResponse(), json_decode(substr($resp, $header_len)));

        if (!is_null($upload->getMessage())) {
            throw new InstagramException($upload->getMessage()."\n");

            return;
        }
        */

        if ($this->parent->debug) {
            Debug::printRequest('POST', $uploadUrl);

            if ($response->hasHeader('x-encoded-content-length')) {
                $bytes = Utils::formatBytes($response->getHeader('x-encoded-content-length')[0]);
            } else {
                $bytes = Utils::formatBytes($response->getHeader('Content-Length')[0]);
            }
            $httpCode = $response->getStatusCode();
            Debug::printHttpCode($httpCode, $bytes);
            Debug::printResponse($body, $this->parent->truncatedDebug);
        }
        exit();

        if ($this->parent->settingsAdapter['type'] == 'mysql') {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        } elseif ($this->parent->settings->setting instanceof SettingsAdapter\SettingsInterface) {
            $newCookies = file_get_contents($cookieJarFile);
            $this->parent->settings->set('cookies', $newCookies);
        }
        $configure = $this->parent->configureVideo($upload_id, $video, $caption, $customPreview);
        //$this->parent->expose();
        $attemps = 0;
        while ($configure->getMessage() == 'Transcode timeout' && $attemps < 3) {
            sleep(1);
            $configure = $this->parent->configureVideo($upload_id, $video, $caption, $customPreview);
            $attemps++;
        }

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
