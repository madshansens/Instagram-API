<?php

namespace InstagramAPI;

class Instagram
{
    /**
     * Currently active Instagram username.
     *
     * @var string
     */
    public $username;

    /**
     * Currently active Instagram password.
     *
     * @var string
     */
    public $password;

    /**
     * The Android Device for the currently active user.
     *
     * @var \InstagramAPI\Devices\Device
     */
    public $device;

    /**
     * Toggles API query/response debug output.
     *
     * @var bool
     */
    public $debug;

    /**
     * Toggles truncating long responses when debugging.
     *
     * @var bool
     */
    public $truncatedDebug;

    /**
     * For internal use by Instagram-API developers!
     *
     * Toggles the throwing of exceptions whenever Instagram-API's "Response"
     * classes lack fields that were provided by the server. Useful for
     * discovering that our library classes need updating.
     *
     * This is only settable via this public property and is NOT meant for
     * end-users of this library. It is for contributing developers!
     *
     * @var bool
     */
    public $apiDeveloperDebug = false;

    /**
     * UUID.
     *
     * @var string
     */
    public $uuid;

    /**
     * Device ID.
     *
     * @var string
     */
    public $device_id;

    /**
     * Numerical UserPK ID of the active user.
     *
     * @var string
     */
    public $username_id;

    /**
     * csrftoken.
     *
     * @var string
     */
    public $token;

    /**
     * Session status.
     *
     * @var bool
     */
    public $isLoggedIn = false;

    /**
     * Rank token.
     *
     * @var string
     */
    public $rank_token;

    /**
     * Raw API communication/networking class.
     *
     * @var Client
     */
    public $client;

    /**
     * The configuration used for initializing our settings adapter.
     *
     * @var array|null
     */
    public $settingsAdapter;

    /**
     * Our settings storage adapter instance.
     *
     * @var \InstagramAPI\Settings\Adapter|null
     */
    public $settings;

    /**
     * Constructor.
     *
     * @param bool $debug           Show API queries and responses.
     * @param bool $truncatedDebug  Truncate long responses.
     * @param null $settingsAdapter How to store session settings.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function __construct(
        $debug = false,
        $truncatedDebug = false,
        $settingsAdapter = null)
    {
        $this->debug = $debug;
        $this->truncatedDebug = $truncatedDebug;

        $longOpts = ['settings_adapter::'];
        $options = getopt('', $longOpts);

        if (!$options) {
            $options = [];
        }

        if (!is_null($settingsAdapter)) {
            $this->settingsAdapter = $settingsAdapter;
        } elseif (array_key_exists('settings_adapter', $options)) {
            $this->settingsAdapter = ['type' => $options['settings_adapter']];
        } elseif (getenv('SETTINGS_ADAPTER') !== false) {
            $this->settingsAdapter = ['type' => getenv('SETTINGS_ADAPTER')];
        } else {
            $this->settingsAdapter = ['type' => 'file'];
        }

        $this->client = new Client($this);
    }

    /**
     * Set the active account for the class instance.
     *
     * You can call this multiple times to switch between multiple accounts.
     *
     * @param string $username Your Instagram username.
     * @param string $password Your Instagram password.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function setUser(
        $username,
        $password)
    {
        if (empty($username) || empty($password)) {
            throw new \InvalidArgumentException('You must provide a username and password to setUser().');
        }

        $this->settings = new Settings\Adapter($this->settingsAdapter, $username);

        // Generate the user's Device instance, which will be created from the
        // user's last-used device IF they've got a valid, good one stored.
        // But if they've got a BAD/none, this will create a brand-new device.
        $savedDeviceString = $this->settings->get('devicestring');
        $this->device = new Devices\Device($savedDeviceString);

        // Save the chosen device string to settings if not already stored.
        $deviceString = $this->device->getDeviceString();
        if ($deviceString !== $savedDeviceString) {
            $this->settings->set('devicestring', $deviceString);
        }

        // Generate a brand-new device fingerprint if the Device wasn't reused
        // from settings, OR if any of the stored fingerprints are missing.
        // NOTE: The regeneration when our device model changes is to avoid
        // dangerously reusing the "previous phone's" unique hardware IDs.
        $resetCookieJar = false;
        if ($deviceString !== $savedDeviceString
            || empty($this->settings->get('uuid'))
            || empty($this->settings->get('phone_id'))
            || empty($this->settings->get('device_id'))) {
            // Generate new hardware fingerprints.
            $this->settings->set('device_id', Signatures::generateDeviceId());
            $this->settings->set('phone_id', Signatures::generateUUID(true));
            $this->settings->set('uuid', Signatures::generateUUID(true));

            // Remove the previous hardware's login details to force a relogin.
            $this->settings->set('username_id', '');
            $this->settings->set('token', '');
            $this->settings->set('last_login', '0');

            // We'll also need to throw out all previous cookies.
            $resetCookieJar = true;
        }

        // Store various important parameters.
        $this->username = $username;
        $this->password = $password;
        $this->uuid = $this->settings->get('uuid');
        $this->device_id = $this->settings->get('device_id');

        // Load the previous session details if we're possibly logged in.
        if (!$resetCookieJar && $this->settings->maybeLoggedIn()) {
            $this->isLoggedIn = true;
            $this->username_id = $this->settings->get('username_id');
            $this->rank_token = $this->username_id.'_'.$this->uuid;
            $this->token = $this->settings->get('token');
        } else {
            $this->isLoggedIn = false;
            $this->username_id = null;
            $this->rank_token = null;
            $this->token = null;
        }

        // Configures Client for current user AND updates isLoggedIn state
        // if it fails to load the expected cookies from the user's jar.
        // Must be done last here, so that isLoggedIn is properly updated!
        // NOTE: If we generated a new device we start a new cookie jar.
        $this->client->updateFromSettingsAdapter($resetCookieJar);
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
        $this->client->setVerifySSL($state);
    }

    /**
     * Gets the current SSL verification behavior of the Client.
     *
     * @return bool|string
     */
    public function getVerifySSL()
    {
        return $this->client->getVerifySSL();
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
        $this->client->setProxy($value);
    }

    /**
     * Gets the current proxy used for requests.
     *
     * @return string|array|null
     */
    public function getProxy()
    {
        return $this->client->getProxy();
    }

    /**
     * Sets the network interface override to use.
     *
     * Only works if Guzzle is using the cURL backend. But that's
     * almost always the case, on most PHP installations.
     *
     * @see CURLOPT_INTERFACE (http://php.net/curl_setopt)
     *
     * @var string|null Interface name, IP address or hostname, or NULL to
     *                  disable override and let Guzzle use any interface.
     */
    public function setOutputInterface(
        $value)
    {
        $this->client->setOutputInterface($value);
    }

    /**
     * Gets the current network interface override used for requests.
     *
     * @return string|null
     */
    public function getOutputInterface()
    {
        return $this->client->getOutputInterface();
    }

    /**
     * Login to Instagram or automatically resume and refresh previous session.
     *
     * WARNING: You MUST run this function EVERY time your script runs! It handles automatic session
     * resume and relogin and app session state refresh and other absolutely *vital* things that are
     * important if you don't want to be banned from Instagram!
     *
     * @param bool $forceLogin         Force login to Instagram, this will create a new session.
     * @param int  $appRefreshInterval How frequently login() should act like an Instagram app
     *                                 that's been closed and reopened and needs to "refresh its
     *                                 state", by asking for extended account state details.
     *                                 Default: After 1800 seconds, meaning 30 minutes since the
     *                                 last state-refreshing login() call.
     *                                 This CANNOT be longer than 6 hours. Read code to see why!
     *                                 The shorter your delay is the BETTER. You may even want to
     *                                 set it to an even LOWER value than the default 30 minutes!
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ExploreResponse
     */
    public function login(
        $forceLogin = false,
        $appRefreshInterval = 1800)
    {
        // Perform a full relogin if necessary.
        if (!$this->isLoggedIn || $forceLogin) {
            $this->syncFeatures(true);

            $response = $this->request('si/fetch_headers')
            ->requireLogin(true)
            ->addParams('challenge_type', 'signup')
            ->addParams('guid', $this->uuid)
            ->getResponse(new Response\ChallengeResponse(), true);

            $response = $this->request('accounts/login/')
            ->requireLogin(true)
            ->addPost('phone_id', $this->settings->get('phone_id'))
            ->addPost('_csrftoken', $response->getFullResponse()[0])
            ->addPost('username', $this->username)
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('password', $this->password)
            ->addPost('login_attempt_count', 0)
            ->getResponse(new Response\LoginResponse(), true);

            $this->isLoggedIn = true;
            $this->username_id = $response->getLoggedInUser()->getPk();
            $this->settings->set('username_id', $this->username_id);
            $this->rank_token = $this->username_id.'_'.$this->uuid;
            $this->token = $response->getFullResponse()[0];
            $this->settings->set('token', $this->token);
            $this->settings->set('last_login', time());

            $this->syncFeatures();
            $this->getAutoCompleteUserList();
            $this->getTimelineFeed();
            $this->getRankedRecipients();
            $this->getRecentRecipients();
            $this->getMegaphoneLog();
            $this->getV2Inbox();
            $this->getRecentActivity();
            $this->getReelsTrayFeed();

            return $this->getExplore();
        }

        // Act like a real logged in app client refreshing its news timeline.
        // This also lets us detect if we're still logged in with a valid session.
        try {
            $this->getTimelineFeed();
        } catch (\InstagramAPI\Exception\LoginRequiredException $e) {
            // If our session cookies are expired, we were now told to login,
            // so handle that by running a forced relogin in that case!
            return $this->login(true, $appRefreshInterval);
        }

        // SUPER IMPORTANT:
        //
        // STOP trying to ask us to remove this code section!
        //
        // EVERY time the user presses their device's home button to leave the
        // app and then comes back to the app, Instagram does ALL of these things
        // to refresh its internal app state. We MUST emulate that perfectly,
        // otherwise Instagram will silently detect you as a "fake" client
        // after a while!
        //
        // You can configure the login's $appRefreshInterval in the function
        // parameter above, but you should keep it VERY frequent (definitely
        // NEVER longer than 6 hours), so that Instagram sees you as a real
        // client that keeps quitting and opening their app like a REAL user!
        //
        // Otherwise they WILL detect you as a bot and silently BLOCK features
        // or even ban you.
        //
        // You have been warned.
        if ($appRefreshInterval > 21600) {
            throw new \InvalidArgumentException("Instagram's app state refresh interval is NOT allowed to be higher than 6 hours, and the lower the better!");
        }
        $lastLoginTime = $this->settings->get('last_login');
        if (is_null($lastLoginTime) || (time() - $lastLoginTime) > $appRefreshInterval) {
            $this->settings->set('last_login', time());

            $this->getAutoCompleteUserList();
            $this->getReelsTrayFeed();
            $this->getRankedRecipients();
            //push register
            $this->getRecentRecipients();
            //push register
            $this->getMegaphoneLog();
            $this->getV2Inbox();
            $this->getRecentActivity();

            return $this->getExplore();
        }
    }

    /**
     * Log out of Instagram.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LogoutResponse
     */
    public function logout()
    {
        return $this->request('accounts/logout/')->getResponse(new Response\LogoutResponse());
    }

    /**
     * Perform an Instagram "feature synchronization" call.
     *
     * @param bool $prelogin
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SyncResponse
     */
    public function syncFeatures(
        $prelogin = false)
    {
        if ($prelogin) {
            return $this->request('qe/sync/')
            ->requireLogin(true)
            ->addPost('id', Signatures::generateUUID(true))
            ->addPost('experiments', Constants::LOGIN_EXPERIMENTS)
            ->getResponse(new Response\SyncResponse());
        } else {
            return $this->request('qe/sync/')
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->username_id)
            ->addPost('_csrftoken', $this->token)
            ->addPost('id', $this->username_id)
            ->addPost('experiments', Constants::EXPERIMENTS)
            ->getResponse(new Response\SyncResponse());
        }
    }

    /**
     * Retrieve list of all friends.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AutoCompleteUserListResponse|null Will be NULL if throttled by Instagram.
     */
    public function getAutoCompleteUserList()
    {
        // NOTE: This is a special, very heavily throttled API endpoint.
        // Instagram REQUIRES that you wait several minutes between calls to it.
        try {
            $request = $this->request('friendships/autocomplete_user_list/')
            ->setCheckStatus(false)
            ->addParams('version', '2');

            return $request->getResponse(new Response\AutoCompleteUserListResponse());
        } catch (\InstagramAPI\Exception\ThrottledException $e) {
            // Throttling is so common that we'll simply return NULL in that case.
            return;
        }
    }

    /**
     * Register to the mqtt push server.
     *
     * TODO: NOT IMPLEMENTED YET!
     *
     * @param $gcmToken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return mixed
     */
    public function pushRegister(
        $gcmToken)
    {
        $deviceToken = json_encode([
            'k' => $gcmToken,
            'v' => 0,
            't' => 'fbns-b64',
        ]);

        $data = json_encode([
            '_uuid'                => $this->uuid,
            'guid'                 => $this->uuid,
            'phone_id'             => $this->settings->get('phone_id'),
            'device_type'          => 'android_mqtt',
            'device_token'         => $deviceToken,
            'is_main_push_channel' => true,
            '_csrftoken'           => $this->token,
            'users'                => $this->username_id,
        ]);

        return $this->client->api('push/register/?platform=10&device_type=android_mqtt', Signatures::generateSignature($data))[1];
    }

    /**
     * Get your own timeline feed.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TimelineFeedResponse
     */
    public function getTimelineFeed(
        $maxId = null)
    {
        $request = $this->request('feed/timeline')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', true);
        if ($maxId) {
            $request->addParams('max_id', $maxId);
        }

        return $request->getResponse(new Response\TimelineFeedResponse());
    }

    /**
     * Get insights.
     *
     * @param $day
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\InsightsResponse
     */
    public function getInsights(
        $day = null)
    {
        if (empty($day)) {
            $day = date('d');
        }
        $request = $this->request('insights/account_organic_insights')
        ->addParams('show_promotions_in_landing_page', 'true')
        ->addParams('first', $day);

        return $request->getResponse(new Response\InsightsResponse());
    }

    /**
     * Get media insights.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaInsightsResponse
     */
    public function getMediaInsights(
        $mediaId)
    {
        $request = $this->request("insights/media_organic_insights/{$mediaId}")
        ->setSignedPost(true)
        ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION);

        return $request->getResponse(new Response\MediaInsightsResponse());
    }

    /**
     * Get megaphone log.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MegaphoneLogResponse
     */
    public function getMegaphoneLog()
    {
        return $this->request('megaphone/log/')
        ->setSignedPost(false)
        ->addPost('type', 'feed_aysf')
        ->addPost('action', 'seen')
        ->addPost('reason', '')
        ->addPost('_uuid', $this->uuid)
        ->addPost('device_id', $this->device_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('uuid', md5(time()))
        ->getResponse(new Response\MegaphoneLogResponse());
    }

    /**
     * Get pending inbox data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PendingInboxResponse
     */
    public function getPendingInbox()
    {
        return $this->request('direct_v2/pending_inbox')->getResponse(new Response\PendingInboxResponse());
    }

    /**
     * Get ranked list of recipients.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RankedRecipientsResponse
     */
    public function getRankedRecipients()
    {
        return $this->request('direct_v2/ranked_recipients')
        ->addParams('show_threads', true)
        ->getResponse(new Response\RankedRecipientsResponse());
    }

    /**
     * Get recent recipients.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RecentRecipientsResponse
     */
    public function getRecentRecipients()
    {
        return $this->request('direct_share/recent_recipients/')
        ->getResponse(new Response\RecentRecipientsResponse());
    }

    /**
     * Get Explore tab data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ExploreResponse
     */
    public function getExplore()
    {
        return $this->request('discover/explore/')->getResponse(new Response\ExploreResponse());
    }

    /**
     * Get Home channel data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DiscoverChannelsResponse
     */
    public function getDiscoverChannels()
    {
        return $this->request('discover/channels_home/')->getResponse(new Response\DiscoverChannelsResponse());
    }

    /**
     * Get top live broadcasts.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DiscoverTopLiveResponse
     */
    public function getDiscoverTopLive()
    {
        return $this->request('discover/top_live/')->getResponse(new Response\DiscoverTopLiveResponse());
    }

    /**
     * Expose.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ExposeResponse
     */
    public function expose()
    {
        return $this->request('qe/expose/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('id', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('experiment', 'ig_android_profile_contextual_feed')
        ->getResponse(new Response\ExposeResponse());
    }

    /**
     * INTERNAL.
     *
     * @param string     $type          What type of upload ("timeline" or "story",
     *                                  but not "album". They're handled elsewhere.)
     * @param string     $photoFilename The photo filename.
     * @param array|null $metadata      (optional) Metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see configure() for available metadata fields.
     */
    protected function _uploadPhoto(
        $type,
        $photoFilename,
        array $metadata = null)
    {
        // Make sure we don't allow "album" photo uploads via this function.
        if ($type != 'timeline' && $type != 'story') {
            throw new \InvalidArgumentException(sprintf('Unsupported photo upload type "%s".', $type));
        }

        $size = getimagesize($photoFilename);
        $metadata['width'] = $size[0];
        $metadata['height'] = $size[1];

        // Perform the upload and then configure it for our timeline/story.
        $upload = $this->client->uploadPhotoData($type, $photoFilename);
        $configure = $this->configure($type, $upload->getUploadId(), $photoFilename, $metadata);

        return $configure;
    }

    /**
     * Uploads a photo to your Instagram timeline.

     * @param string     $photoFilename The photo filename.
     * @param array|null $metadata      (optional) Metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see configure() for available metadata fields.
     */
    public function uploadTimelinePhoto(
        $photoFilename,
        array $metadata = null)
    {
        return $this->_uploadPhoto('timeline', $photoFilename, $metadata);
    }

    /**
     * Uploads a photo to your Instagram story.
     *
     * @param string     $photoFilename The photo filename.
     * @param array|null $metadata      (optional) Metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see configure() for available metadata fields.
     */
    public function uploadStoryPhoto(
        $photoFilename,
        array $metadata = null)
    {
        return $this->_uploadPhoto('story', $photoFilename, $metadata);
    }

    /**
     * INTERNAL.
     *
     * @param string     $type          What type of upload ("timeline" or "story",
     *                                  but not "album". They're handled elsewhere.)
     * @param string     $videoFilename The video filename.
     * @param array|null $metadata      (optional) Metadata key-value pairs.
     * @param int        $maxAttempts   (optional) Total attempts to upload all chunks before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureVideoResponse
     *
     * @see configureVideo() for available metadata fields.
     */
    protected function _uploadVideo(
        $type,
        $videoFilename,
        array $metadata = null,
        $maxAttempts = 10)
    {
        // Make sure we don't allow "album" video uploads via this function.
        if ($type != 'timeline' && $type != 'story') {
            throw new \InvalidArgumentException(sprintf('Unsupported video upload type "%s".', $type));
        }

        // Figure out the video file details.
        // NOTE: We do this first, since it validates whether the video file is
        // valid and lets us avoid wasting time uploading totally invalid files!
        $metadata['videodetails'] = Utils::getVideoFileDetails($videoFilename);

        // Validate the video details and throw if Instagram won't allow it.
        Utils::throwIfIllegalVideoDetails($type, $videoFilename, $metadata['videodetails']);

        // Request parameters for uploading a new video.
        $uploadParams = $this->client->requestVideoUploadURL($type, $metadata);

        // Attempt to upload the video data.
        $upload = $this->client->uploadVideoData($type, $videoFilename, $uploadParams, $maxAttempts);

        // Attempt to upload the thumbnail, associated with our video's ID.
        $this->client->uploadPhotoData($type, $videoFilename, 'videofile', $uploadParams['upload_id']);

        // Configure the uploaded video and attach it to our timeline/story.
        $configure = $this->configureVideoWithRetries($type, $uploadParams['upload_id'], $metadata);

        return $configure;
    }

    /**
     * Uploads a video to your Instagram timeline.
     *
     * @param string     $videoFilename The video filename.
     * @param array|null $metadata      (optional) Metadata key-value pairs.
     * @param int        $maxAttempts   Total attempts to upload all chunks before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureVideoResponse
     *
     * @see configureVideo() for available metadata fields.
     */
    public function uploadTimelineVideo(
        $videoFilename,
        array $metadata = null,
        $maxAttempts = 10)
    {
        return $this->_uploadVideo('timeline', $videoFilename, $metadata, $maxAttempts);
    }

    /**
     * Uploads a video to your Instagram story.
     *
     * @param string     $videoFilename The video filename.
     * @param array|null $metadata      (optional) Metadata key-value pairs.
     * @param int        $maxAttempts   Total attempts to upload all chunks before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureVideoResponse
     *
     * @see configureVideo() for available metadata fields.
     */
    public function uploadStoryVideo(
        $videoFilename,
        array $metadata = null,
        $maxAttempts = 10)
    {
        return $this->_uploadVideo('story', $videoFilename, $metadata, $maxAttempts);
    }

    /**
     * Uploads an album to your Instagram timeline.
     *
     * An album is also known as a "carousel" and "sidecar". They can contain up
     * to 10 photos or videos (at the moment).
     *
     * @param array      $media         Array of image/video files and their per-file
     *                                  metadata (type, file, and optionally usertags).
     *                                  The "type" must be "photo" or "video".
     *                                  The "file" must be its disk path. And
     *                                  the optional "usertags" can only be used
     *                                  on PHOTOS, never on videos.
     * @param array|null $albumMetadata (optional) Metadata key-value pairs for the
     *                                  album itself (its caption, location, etc).
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see configure() for available album metadata fields.
     */
    public function uploadTimelineAlbum(
        $media,
        array $albumMetadata = null)
    {
        if (empty($media)) {
            throw new \InvalidArgumentException("List of media to upload can't be empty.");
        }
        if (count($media) < 2 || count($media) > 10) {
            throw new \InvalidArgumentException(sprintf('Instagram requires that albums contain 2-10 items. You tried to submit %d.', count($media)));
        }

        // Figure out the video file details for ALL videos in the album.
        // NOTE: We do this first, since it validates whether the video files are
        // valid and lets us avoid wasting time uploading totally invalid albums!
        foreach ($media as $key => $item) {
            if ($item['type'] == 'video') {
                $media[$key]['videodetails'] = Utils::getVideoFileDetails($item['file']);

                // Validate the video details and throw if Instagram won't allow it.
                Utils::throwIfIllegalVideoDetails('album', $item['file'], $media[$key]['videodetails']);
            }
        }

        $hasUploadedVideo = false;
        foreach ($media as $key => $item) {
            if (!file_exists($item['file'])) {
                throw new \InvalidArgumentException(sprintf('File "%s" does not exist.', $item['file']));
            }

            switch ($item['type']) {
            case 'photo':
                $result = $this->client->uploadPhotoData('album', $item['file']);
                $media[$key]['upload_id'] = $result->getUploadId();
                break;
            case 'video':
                $hasUploadedVideo = true;

                // Request parameters for uploading a new video.
                $uploadParams = $this->client->requestVideoUploadURL('album');
                $media[$key]['upload_id'] = $uploadParams['upload_id'];

                // Attempt to upload the video data.
                // TODO: Consider adding the final "maxAttempts" parameter and
                // making it configurable in uploadTimelineAlbum's parameters. But first
                // finalize the behavior of uploadTimelineAlbum (we may have to
                // remove the "filter" parameter and making it part of the
                // per-photo configuration array, for example, if Instagram
                // allows per-photo filters inside of albums).
                $this->client->uploadVideoData('album', $item['file'], $uploadParams);

                // Attempt to upload the thumbnail, associated with our video's ID.
                $this->client->uploadPhotoData('album', $item['file'], 'videofile', $uploadParams['upload_id']);

                // We don't call configure! Album videos are configured below instead.
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported album media type "%s".', $item['type']));
            }
        }

        if ($hasUploadedVideo) {
            // TODO: We could write this in a nicer way but this solves it for now.
            // In the future we may want to act like configureVideoWithRetries().
            sleep(5); // Super important to avoid configure-problems on new videos!
        }

        $date = date('Y:m:d H:i:s');

        $uploadRequests = [];
        foreach ($media as $item) {
            switch ($item['type']) {
            case 'photo':
                $photoConfig = [
                    'date_time_original'  => $date,
                    'scene_type'          => 1,
                    'disable_comments'    => false,
                    'upload_id'           => $item['upload_id'],
                    'source_type'         => 0,
                    'scene_capture_type'  => 'standard',
                    'date_time_digitized' => $date,
                    'geotag_enabled'      => false,
                    'camera_position'     => 'back',
                    'edits', [
                        'filter_strength' => 1,
                        'filter_name'     => 'IGNormalFilter',
                    ],
                ];

                // This usertag per-file metadata is only supported for PHOTOS!
                if (isset($item['usertags'])) {
                    $photoConfig['usertags'] = json_encode(['in' => $item['usertags']]);
                }

                $uploadRequests[] = $photoConfig;
                break;
            case 'video':
                $videoConfig = [
                    'length'              => round($item['videodetails']['duration'], 2),
                    'date_time_original'  => $date,
                    'scene_type'          => 1,
                    'poster_frame_index'  => 0,
                    'trim_type'           => 0,
                    'disable_comments'    => false,
                    'upload_id'           => $item['upload_id'],
                    'source_type'         => 'library',
                    'geotag_enabled'      => false,
                    'edits', [
                        //'length'          => '0.00', // TODO! Should this always be 0.00?
                        'cinema'          => 'unsupported',
                        'original_length' => 0.00,
                        'source_type'     => 'library',
                        'start_time'      => 0,
                        'camera_position' => 'unknown',
                        'trim_type'       => 0,
                    ],
                ];

                $uploadRequests[] = $videoConfig;
                break;
            }
        }

        // TODO: THIS SEEMS BUGGED TO ME. Why is it only using the last item's
        // "file" value when configuring a whole array of uploadRequests?
        $configure = $this->configure('album', $uploadRequests, $item['file'], $albumMetadata);

        return $configure;
    }

    /**
     * Share media via direct message to a user's inbox.
     *
     * @param array|int $recipients One or more numeric user IDs.
     * @param string    $mediaId    The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string    $text       Text message.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function directShare(
        $recipients,
        $mediaId,
        $text = null)
    {
        return $this->client->directShare(
            'share',
            $recipients,
            [
                'text'     => $text,
                'media_id' => $mediaId,
            ]
        );
    }

    /**
     * Send a direct message to a user's inbox.
     *
     * @param array|int $recipients One or more numeric user IDs.
     * @param string    $text       Text message.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function directMessage(
        $recipients,
        $text)
    {
        return $this->client->directShare(
            'message',
            $recipients,
            [
                'text' => $text,
            ]
        );
    }

    /**
     * Send a photo via direct message to a user's inbox.
     *
     * @param array|int $recipients    One or more numeric user IDs.
     * @param string    $photoFilename The photo filename.
     * @param string    $text          Text message.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function directPhoto(
        $recipients,
        $photoFilename,
        $text = null)
    {
        return $this->client->directShare(
            'photo',
            $recipients,
            [
                'text'     => $text,
                'filepath' => $photoFilename,
            ]
        );
    }

    /**
     * Get direct message thread.
     *
     * @param string      $threadId Thread ID.
     * @param string|null $cursorId
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DirectThreadResponse
     */
    public function directThread(
        $threadId,
        $cursorId = null)
    {
        $request = $this->request("direct_v2/threads/$threadId/");
        if ($cursorId !== null) {
            $request->addParams('cursor', $cursorId);
        }

        return $request->getResponse(new Response\DirectThreadResponse());
    }

    /**
     * Perform an action on a direct message thread.
     *
     * @param string $threadId     Thread ID.
     * @param string $threadAction Action ("approve", "decline" or "block").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return array Direct thread action server response.
     */
    // TODO : Missing Response object!
    public function directThreadAction(
        $threadId,
        $threadAction)
    {
        $data = json_encode([
            '_uuid'      => $this->uuid,
            '_uid'       => $this->username_id,
            '_csrftoken' => $this->token,
        ]);

        return $this->client->api("direct_v2/threads/{$threadId}/{$threadAction}/", Signatures::generateSignature($data))[1];
    }

    /**
     * Helper function for reliably configuring videos.
     *
     * Exactly the same as configureVideo() but performs multiple attempts. Very
     * useful since Instagram sometimes can't configure a newly uploaded video
     * file until a few seconds have passed.
     *
     * @param string     $type        What type of upload ("timeline" or "story",
     *                                but not "album". They're handled elsewhere.)
     * @param string     $uploadId    The ID of the upload to configure.
     * @param array|null $metadata    (optional) Metadata key-value pairs.
     * @param int        $maxAttempts Total attempts to configure video before throwing.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureVideoResponse
     *
     * @see configureVideo() for available metadata fields.
     */
    public function configureVideoWithRetries(
        $type,
        $uploadId,
        array $metadata = null,
        $maxAttempts = 5)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            try {
                // Attempt to configure video parameters.
                $configure = $this->configureVideo($type, $uploadId, $metadata);
                //$this->expose(); // <-- WTF? Old leftover code.
                break; // Success. Exit loop.
            } catch (\InstagramAPI\Exception\InstagramException $e) {
                if ($attempt < $maxAttempts && strpos($e->getMessage(), 'Transcode timeout') !== false) {
                    // Do nothing, since we'll be retrying the failed configure...
                    sleep(1); // Just wait a little before the next retry.
                } else {
                    // Re-throw all unhandled exceptions.
                    throw $e;
                }
            }
        }

        return $configure; // ConfigureVideoResponse
    }

    /**
     * Configure parameters for uploaded video.
     *
     * @param string     $type     What type of upload ("timeline" or "story",
     *                             but not "album". They're handled elsewhere.)
     * @param string     $uploadId The ID of the upload to configure.
     * @param array|null $metadata (optional) Metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureVideoResponse
     */
    public function configureVideo(
        $type,
        $uploadId,
        array $metadata = null)
    {
        // Available metadata parameters:
        /** @var string|null Caption to use for the media. */
        $captionText = isset($metadata['caption']) ? $metadata['caption'] : null;
        /** @var string[]|null Array of UserPK IDs of people tagged in your
         * video. ONLY USED IN STORY VIDEOS! TODO: Actually, it's not even implemented for stories. */
        $userTags = (isset($metadata['usertags']) && $type == 'story') ? $metadata['usertags'] : null;
        /** @var Response\Model\Location|null A Location object describing where
         the media was taken. NOT USED FOR STORY MEDIA! */
        $location = (isset($metadata['location']) && $type != 'story') ? $metadata['location'] : null;

        // Make sure we don't configure "album" video uploads via this function.
        switch ($type) {
        case 'timeline':
            $endpoint = 'media/configure/';
            break;
        case 'story':
            $endpoint = 'media/configure_to_story/';
            break;
        default:
            throw new \InvalidArgumentException('Invalid video configuration type.');
        }

        $requestData = $this->request($endpoint)
        ->addParams('video', 1)
        ->addPost('video_result', 'deprecated')
        ->addPost('upload_id', $uploadId)
        ->addPost('poster_frame_index', 0)
        ->addPost('length', round($metadata['videodetails']['duration'], 1))
        ->addPost('audio_muted', false)
        ->addPost('filter_type', 0)
        ->addPost('source_type', 4)
        ->addPost('video_result', 'deprecated')
        ->addPost('device',
            [
                'manufacturer'      => $this->device->getManufacturer(),
                'model'             => $this->device->getModel(),
                'android_version'   => $this->device->getAndroidVersion(),
                'android_release'   => $this->device->getAndroidRelease(),
            ])
        ->addPost('extra',
            [
                'source_width'  => $metadata['videodetails']['width'],
                'source_height' => $metadata['videodetails']['height'],
            ])
        ->addPost('_csrftoken', $this->token)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id);

        if ($type == 'story') {
            $requestData->addPost('configure_mode', 1) // 1 - REEL_SHARE, 2 - DIRECT_STORY_SHARE
            ->addPost('story_media_creation_date', time() - mt_rand(10, 20))
            ->addPost('client_shared_at', time() - mt_rand(3, 10))
            ->addPost('client_timestamp', time());
        }

        if ($captionText !== '' && !is_null($captionText) && $captionText) {
            $requestData->addPost('caption', $captionText);
        }

        // TODO: IMPLEMENT THIS "STORY USER TAGS" FEATURE!
        // Reel Mention example --> build with user id
        // [{\"y\":0.3407772676161919,\"rotation\":0,\"user_id\":\"USER_ID\",\"x\":0.39892578125,\"width\":0.5619921875,\"height\":0.06011525487256372}]
        if ($type == 'story') {
            $requestData->addPost('story_media_creation_date', time());
            if (!is_null($userTags)) {
                //$requestData->addPost('reel_mentions', addcslashes(json_encode($userTags)));
            }
        }

        if ($location instanceof Response\Model\Location) {
            $loc = [
                $location->getExternalIdSource().'_id'   => $location->getExternalId(),
                'name'                                   => $location->getName(),
                'lat'                                    => $location->getLat(),
                'lng'                                    => $location->getLng(),
                'address'                                => $location->getAddress(),
                'external_source'                        => $location->getExternalIdSource(),
            ];

            $requestData->addPost('location', json_encode($loc))
            ->addPost('geotag_enabled', '1')
            ->addPost('av_latitude', 0.0)
            ->addPost('av_longitude', 0.0)
            ->addPost('posting_latitude', $location->getLat())
            ->addPost('posting_longitude', $location->getLng())
            ->addPost('media_latitude', $location->getLat())
            ->addPost('media_longitude', $location->getLng());
        }

        $configure = $requestData->getResponse(new Response\ConfigureVideoResponse());

        return $configure;
    }

    /**
     * Configure uploaded media parameters (primarily for photos, but also albums).
     *
     * @param string     $type          What type of entry ("timeline", "story" or "album").
     * @param string     $uploadId      The ID of the entry to configure.
     * @param string     $photoFilename The photo filename.
     * @param array|null $metadata      (optional) Metadata key-value pairs.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configure(
        $type,
        $uploadId,
        $photoFilename,
        array $metadata = null)
    {
        // Available metadata parameters:
        /** @var string|null Caption to use for the media. */
        $captionText = isset($metadata['caption']) ? $metadata['caption'] : null;
        /** @var Response\Model\Location|null A Location object describing where
         the media was taken. NOT USED FOR STORY MEDIA! */
        $location = (isset($metadata['location']) && $type != 'story') ? $metadata['location'] : null;
        /** @var void Photo filter. THIS DOES NOTHING! All real filters are done in the mobile app. */
        // $filter = isset($metadata['filter']) ? $metadata['filter'] : null;
        $filter = null; // COMMENTED OUT SO USERS UNDERSTAND THEY CAN'T USE THIS!

        // Begin...
        $size = getimagesize($photoFilename)[0];
        if (is_null($captionText)) {
            $captionText = '';
        }

        if ($type == 'album') {
            $endpoint = 'media/configure_sidecar/';
        } elseif ($type == 'story') {
            $endpoint = 'media/configure_to_story/';
        } else {
            $endpoint = 'media/configure/';
        }

        $requestData = $this->request($endpoint)
        ->addPost('_csrftoken', $this->token)
        ->addPost('_uid', $this->username_id)
        ->addPost('_uuid', $this->uuid)
        ->addPost('device',
            [
                'manufacturer'      => $this->device->getManufacturer(),
                'model'             => $this->device->getModel(),
                'android_version'   => $this->device->getAndroidVersion(),
                'android_release'   => $this->device->getAndroidRelease(),
            ]
        )
        ->addPost('edits',
            [
                'crop_original_size'    => [$metadata['width'], $metadata['height']],
                'crop_zoom'             => 1,
                'crop_center'           => [0.0, -0.0],
            ]
        )
        ->addPost('extra',
            [
                'source_width'  => $metadata['width'],
                'source_height' => $metadata['height'],
            ]
        );

        switch ($type) {
            case 'timeline':
                $requestData->addPost('caption', $captionText)
                ->addPost('source_type', 4)
                ->addPost('media_folder', 'Camera')
                ->addPost('upload_id', $uploadId);
                break;
            case 'story':
                $requestData->addPost('client_shared_at', time())
                ->addPost('source_type', 3)
                ->addPost('configure_mode', 1)
                ->addPost('client_timestamp', time())
                ->addPost('upload_id', $uploadId);
                break;
            case 'album':
                $requestData->addPost('client_sidecar_id', Utils::generateUploadId())
                ->addPost('children_metadata', $uploadId);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported media type "%s".', $type));
        }

        if ($location instanceof Response\Model\Location) {
            $loc = [
                $location->getExternalIdSource().'_id'   => $location->getExternalId(),
                'name'                                   => $location->getName(),
                'lat'                                    => $location->getLat(),
                'lng'                                    => $location->getLng(),
                'address'                                => $location->getAddress(),
                'external_source'                        => $location->getExternalIdSource(),
            ];

            $requestData->addPost('location', json_encode($loc))
            ->addPost('geotag_enabled', '1')
            ->addPost('posting_latitude', $location->getLat())
            ->addPost('posting_longitude', $location->getLng())
            ->addPost('media_latitude', $location->getLat())
            ->addPost('media_longitude', $location->getLng());

            if ($type == 'album') {
                $requestData->addPost('location', json_encode($loc))
                ->addPost('exif_latitude', 0.0)
                ->addPost('exif_longitude', 0.0);
            } else {
                $requestData->addPost('location', json_encode($loc))
                ->addPost('av_latitude', 0.0)
                ->addPost('av_longitude', 0.0);
            }
        }

        $configure = $requestData->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Edit media.
     *
     * @param string   $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string   $captionText Caption text.
     * @param string[] $userTags    Array of UserPK IDs of people tagged in your media.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditMediaResponse
     */
    public function editMedia(
        $mediaId,
        $captionText = '',
        $usertags = null)
    {
        if (is_null($usertags)) {
            return $this->request("media/{$mediaId}/edit_media/")
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->username_id)
            ->addPost('_csrftoken', $this->token)
            ->addPost('caption_text', $captionText)
            ->getResponse(new Response\EditMediaResponse());
        } else {
            return $this->request("media/{$mediaId}/edit_media/")
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->username_id)
            ->addPost('_csrftoken', $this->token)
            ->addPost('caption_text', $captionText)
            ->addPost('usertags', $usertags)
            ->getResponse(new Response\EditMediaResponse());
        }
    }

    /**
     * Tag a user in a media item.
     *
     * @param string      $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string      $userId      Numerical UserPK ID.
     * @param array|float $position    Position relative to image where the tag should sit. Example: [0.4890625,0.6140625]
     * @param string      $captionText Caption text.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditMediaResponse
     */
    public function tagUser(
        $mediaId,
        $userId,
        $position,
        $captionText = '')
    {
        $usertag = '{"removed":[],"in":[{"position":['.$position[0].','.$position[1].'],"user_id":"'.$userId.'"}]}';

        return $this->editMedia($mediaId, $captionText, $usertag);
    }

    /**
     * Untag a user from a media item.
     *
     * @param string $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $userId      Numerical UserPK ID.
     * @param string $captionText Caption text.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\EditMediaResponse
     */
    public function untagUser(
        $mediaId,
        $userId,
        $captionText = '')
    {
        $usertag = '{"removed":["'.$userId.'"],"in":[]}';

        return $this->editMedia($mediaId, $captionText, $usertag);
    }

    /**
     * Save a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SaveAndUnsaveMedia
     */
    public function saveMedia(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/save/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(true)
        ->getResponse(new Response\SaveAndUnsaveMedia());
    }

    /**
     * Unsave a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SaveAndUnsaveMedia
     */
    public function unsaveMedia(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/unsave/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(true)
        ->getResponse(new Response\SaveAndUnsaveMedia());
    }

    /**
     * Get saved media items feed.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SavedFeedResponse
     */
    public function getSavedFeed()
    {
        return $this->request('feed/saved/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(true)
        ->getResponse(new Response\SavedFeedResponse());
    }

    /**
     * Remove yourself from a tagged media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaResponse
     */
    public function removeSelfTag(
        $mediaId)
    {
        return $this->request("usertags/{$mediaId}/remove/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\MediaResponse());
    }

    /**
     * Get detailed media information.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaInfoResponse
     */
    public function getMediaInfo(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/info/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new Response\MediaInfoResponse());
    }

    /**
     * Delete a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaDeleteResponse
     */
    public function deleteMedia(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/delete/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new Response\MediaDeleteResponse());
    }

    /**
     * Disable comments for a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function disableMediaComments(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/disable_comments/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(false)
        ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Enable comments for a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function enableMediaComments(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/enable_comments/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(false)
        ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Post a comment on a media item.
     *
     * @param string $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentText Your comment text.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentResponse
     */
    public function comment(
        $mediaId,
        $commentText)
    {
        return $this->request("media/{$mediaId}/comment/")
        ->addPost('user_breadcrumb', Utils::generateUserBreadcrumb(mb_strlen($commentText)))
        ->addPost('idempotence_token', Signatures::generateUUID(true))
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('comment_text', $commentText)
        ->addPost('containermodule', 'comments_feed_timeline')
        ->getResponse(new Response\CommentResponse());
    }

    /**
     * Delete a comment.
     *
     * @param string $mediaId   The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DeleteCommentResponse
     */
    public function deleteComment(
        $mediaId,
        $commentId)
    {
        return $this->request("media/{$mediaId}/comment/{$commentId}/delete/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\DeleteCommentResponse());
    }

    /**
     * Delete multiple comments.
     *
     * @param string $mediaId    The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentIds List of comment IDs to delete.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DeleteCommentResponse
     */
    public function deleteComments(
        $mediaId,
        $commentIds)
    {
        if (!is_array($commentIds)) {
            $commentIds = [$commentIds];
        }

        $string = [];
        foreach ($commentIds as $commentId) {
            $string[] = "$commentId";
        }

        $comment_ids_to_delete = implode(',', $string);

        return $this->request("media/{$mediaId}/comment/bulk_delete/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('comment_ids_to_delete', $comment_ids_to_delete)
        ->getResponse(new Response\DeleteCommentResponse());
    }

    /**
     * Like a comment.
     *
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentLikeUnlikeResponse
     */
    public function likeComment(
        $commentId)
    {
        return $this->request("media/{$commentId}/comment_like/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\CommentLikeUnlikeResponse());
    }

    /**
     * Unlike a comment.
     *
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentLikeUnlikeResponse
     */
    public function unlikeComment(
        $commentId)
    {
        return $this->request("media/{$commentId}/comment_unlike/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\CommentLikeUnlikeResponse());
    }

    /**
     * Changes your account's profile picture.
     *
     * @param string $photoFilename The photo filename.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\Model\User
     */
    public function changeProfilePicture(
        $photoFilename)
    {
        return $this->client->changeProfilePicture($photoFilename);
    }

    /**
     * Remove your account's profile picture.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function removeProfilePicture()
    {
        return $this->request('accounts/remove_profile_picture/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Sets your account to private.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setPrivateAccount()
    {
        return $this->request('accounts/set_private/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Sets your account to public.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function setPublicAccount()
    {
        return $this->request('accounts/set_public/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Get details about the currently logged in account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function getCurrentUser()
    {
        return $this->request('accounts/current_user/')
        ->addParams('edit', true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Edit your profile.
     *
     * @param string $url       Website URL. Use "" for nothing.
     * @param string $phone     Phone number. Use "" for nothing.
     * @param string $firstName Name. Use "" for nothing.
     * @param string $biography Biography text. Use "" for nothing.
     * @param string $email     Email. Required.
     * @param int    $gender    Gender. Male = 1, Female = 2, Unknown = 3.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function editProfile(
        $url,
        $phone,
        $firstName,
        $biography,
        $email,
        $gender)
    {
        return $this->request('accounts/edit_profile/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('external_url', $url)
        ->addPost('phone_number', $phone)
        ->addPost('username', $this->username)
        ->addPost('first_name', $firstName)
        ->addPost('biography', $biography)
        ->addPost('email', $email)
        ->addPost('gender', $gender)
        ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Change your account's password.
     *
     * @param string $oldPassword Old password.
     * @param string $newPassword New password.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ChangePasswordResponse
     */
    public function changePassword(
        $oldPassword,
        $newPassword)
    {
        return $this->request('accounts/change_password/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('old_password', $oldPassword)
        ->addPost('new_password1', $newPassword)
        ->addPost('new_password2', $newPassword)
        ->getResponse(new Response\ChangePasswordResponse());
    }

    /**
     * Get recent activity.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ActivityNewsResponse
     */
    public function getRecentActivity()
    {
        return $this->request('news/inbox/')->addParams('activity_module', 'all')->getResponse(new Response\ActivityNewsResponse());
    }

    /**
     * Get news feed with recent activity from all accounts you follow.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FollowingRecentActivityResponse
     */
    public function getFollowingRecentActivity(
        $maxId = null)
    {
        $activity = $this->request('news/');
        if (!is_null($maxId)) {
            $activity->addParams('max_id', $maxId);
        }

        return $activity->getResponse(new Response\FollowingRecentActivityResponse());
    }

    /**
     * Get direct inbox messages for your account.
     *
     * @param string|null $cursorId
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\V2InboxResponse
     */
    public function getV2Inbox(
        $cursorId = null)
    {
        $request = $this->request('direct_v2/inbox/');
        if ($cursorId !== null) {
            $request->addParams('cursor', $cursorId);
        }

        return $request->getResponse(new Response\V2InboxResponse());
    }

    /**
     * Get user taggings for a user.
     *
     * @param string      $userId       Numerical UserPK ID.
     * @param null|string $maxId        Next "maximum ID", used for pagination.
     * @param null|int    $minTimestamp Minimum timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UsertagsResponse
     */
    public function getUserTags(
        $userId,
        $maxId = null,
        $minTimestamp = null)
    {
        return $this->request("usertags/{$userId}/feed/")
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', 'true')
        ->addParams('max_id', (!is_null($maxId) ? $maxId : ''))
        ->addParams('min_timestamp', (!is_null($minTimestamp) ? $minTimestamp : ''))
        ->getResponse(new Response\UsertagsResponse());
    }

    /**
     * Get user taggings for your own account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UsertagsResponse
     */
    public function getSelfUserTags()
    {
        return $this->getUserTags($this->username_id);
    }

    /**
     * Get list of users who liked a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaLikersResponse
     */
    public function getMediaLikers(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/likers/")->getResponse(new Response\MediaLikersResponse());
    }

    /**
     * Facebook user search.
     *
     * @param string $query
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FBSearchResponse
     */
    public function searchFBUsers(
        $query)
    {
        return $this->request('fbsearch/topsearch/')
        ->addParams('context', 'blended')
        ->addParams('query', $query)
        ->addParams('rank_token', $this->rank_token)
        ->getResponse(new Response\FBSearchResponse());
    }

    /**
     * Search for Instagram users.
     *
     * @param string $query
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SearchUserResponse
     */
    public function searchUsers(
        $query)
    {
        return $this->request('users/search/')
        ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION)
        ->addParams('is_typeahead', true)
        ->addParams('query', $query)
        ->addParams('rank_token', $this->rank_token)
        ->getResponse(new Response\SearchUserResponse());
    }

    /**
     * Search for users via address book.
     *
     * @param array $contacts
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AddressBookResponse
     */
    public function searchInAddressBook(
        $contacts)
    {
        return $this->request('address_book/link/?include=extra_display_name,thumbnails')
            ->setSignedPost(false)
            ->addPost('contacts', json_encode($contacts, true))
            ->getResponse(new Response\AddressBookResponse());
    }

    /**
     * Get details about a specific user via their username.
     *
     * @param string $username Username as string (NOT as a numerical ID).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function getUserInfoByName(
        $username)
    {
        return $this->request("users/{$username}/usernameinfo/")->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Get details about a specific user via their UserPK ID.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function getUserInfoById(
        $userId)
    {
        return $this->request("users/{$userId}/info/")->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Get user details about your own account.
     *
     * Also try getCurrentUser() instead, for even more details.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     *
     * @see getCurrentUser()
     */
    public function getSelfUserInfo()
    {
        return $this->getUserInfoById($this->username_id);
    }

    /**
     * Get the UserPK ID for a specific user via their username.
     *
     * This is just a convenient helper function. You may prefer to use
     * getUserInfoByName() instead, which lets you see more details.
     *
     * @param string $username Username as string (NOT as a numerical ID).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string Their UserPK ID.
     *
     * @see getUserInfoByName()
     */
    public function getUsernameId(
        $username)
    {
        return $this->getUserInfoByName($username)->getUser()->getPk();
    }

    /**
     * Get related tags.
     *
     * @param string $tag
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TagRelatedResponse
     */
    public function getTagRelated(
        $tag)
    {
        return $this->request("tags/{$tag}/related")
        ->addParams('visited', '[{"id":"'.$tag.'","type":"hashtag"}]')
        ->addParams('related_types', '["hashtag"]')
        ->getResponse(new Response\TagRelatedResponse());
    }

    /**
     * Get detailed tag information.
     *
     * @param string $tag
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TagInfoResponse
     */
    public function getTagInfo(
        $tag)
    {
        return $this->request("tags/{$tag}/info")
        ->getResponse(new Response\TagInfoResponse());
    }

    /**
     * Get the global story feed which contains everyone you follow.
     *
     * Note that users will eventually drop out of this list even though they
     * still have stories. So it's always safer to call getUserStoryFeed() if
     * a specific user's story feed matters to you.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsTrayFeedResponse
     *
     * @see getUserStoryFeed()
     */
    public function getReelsTrayFeed()
    {
        return $this->request('feed/reels_tray/')->getResponse(new Response\ReelsTrayFeedResponse());
    }

    /**
     * Get a specific user's story reel feed.
     *
     * This function gets the user's story Reel object directly, which always
     * exists and contains information about the user and their last story even
     * if that user doesn't have any active story anymore.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\Model\Reel
     *
     * @see getUserStoryFeed()
     */
    public function getUserReelMediaFeed(
        $userId)
    {
        return $this->request("feed/user/{$userId}/reel_media/")
        ->getResponse(new Response\Model\Reel());
    }

    /**
     * Get a specific user's story feed with broadcast details.
     *
     * This function gets the story in a roundabout way, with some extra details
     * about the "broadcast". But if there is no story available, this endpoint
     * gives you an empty response.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserStoryFeedResponse
     *
     * @see getUserReelMediaFeed()
     */
    public function getUserStoryFeed(
        $userId)
    {
        return $this->request("feed/user/{$userId}/story/")
        ->getResponse(new Response\UserStoryFeedResponse());
    }

    /**
     * Get multiple users' story feeds at once.
     *
     * @param string|string[] $userList List of numerical UserPK IDs.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReelsMediaResponse
     */
    public function getReelsMediaFeed(
        $userList)
    {
        if (!is_array($userList)) {
            $userList = [$userList];
        }

        $userIDs = [];
        foreach ($userList as $userId) {
            $userIDs[] = "$userId";
        }

        return $this->request('feed/reels_media/')
        ->setSignedPost(true)
        ->addPost('user_ids', $userIDs)
        ->getResponse(new Response\ReelsMediaResponse());
    }

    /**
     * Get a user's timeline feed.
     *
     * @param string      $userId       Numerical UserPK ID.
     * @param null|string $maxId        Next "maximum ID", used for pagination.
     * @param null|int    $minTimestamp Minimum timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserFeedResponse
     */
    public function getUserFeed(
        $userId,
        $maxId = null,
        $minTimestamp = null)
    {
        return $this->request("feed/user/{$userId}/")
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', 'true')
        ->addParams('max_id', (!is_null($maxId) ? $maxId : ''))
        ->addParams('min_timestamp', (!is_null($minTimestamp) ? $minTimestamp : ''))
        ->getResponse(new Response\UserFeedResponse());
    }

    /**
     * Get your own timeline feed.
     *
     * @param null|string $maxId        Next "maximum ID", used for pagination.
     * @param null|int    $minTimestamp Minimum timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserFeedResponse
     */
    public function getSelfUserFeed(
        $maxId = null,
        $minTimestamp = null)
    {
        return $this->getUserFeed($this->username_id, $maxId, $minTimestamp);
    }

    /**
     * Get location based media feed for a user.
     *
     * Note that you probably want getUserFeed() instead, because the
     * geographical feed does not contain all of the user's media.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GeoMediaResponse
     *
     * @see getUserFeed()
     */
    public function getGeoMedia(
        $userId)
    {
        return $this->request("maps/user/{$userId}/")->getResponse(new Response\GeoMediaResponse());
    }

    /**
     * Get location based media feed for your own account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GeoMediaResponse
     */
    public function getSelfGeoMedia()
    {
        return $this->getGeoMedia($this->username_id);
    }

    /**
     * Search for nearby Instagram locations by geographical coordinates.
     *
     * @param $latitude
     * @param $longitude
     * @param null $query
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LocationResponse
     */
    public function searchLocation(
        $latitude,
        $longitude,
        $query = null)
    {
        $locations = $this->request('location_search/')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('latitude', $latitude)
        ->addParams('longitude', $longitude);

        if (is_null($query)) {
            $locations->addParams('timestamp', time());
        } else {
            $locations->addParams('search_query', $query);
        }

        return $locations->getResponse(new Response\LocationResponse());
    }

    /**
     * Search for Facebook locations by name.
     *
     * @param string $query
     * @param int    $count (optional) Facebook will return up to this many results.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FBLocationResponse
     */
    public function searchFBLocation(
        $query,
        $count = null)
    {
        $location = $this->request('fbsearch/places/')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('query', $query);

        if (!is_null($count)) {
            $location->addParams('count', $count);
        }

        return $location->getResponse(new Response\FBLocationResponse());
    }

    /**
     * Search for Facebook locations by geographical location.
     *
     * @param string $lat Latitude.
     * @param string $lng Longitude.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FBLocationResponse
     */
    public function searchFBLocationByPoint(
        $lat,
        $lng)
    {
        return $this->request('fbsearch/places/')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('lat', $lat)
        ->addParams('lng', $lng)
        ->getResponse(new Response\FBLocationResponse());
    }

    /**
     * Get location feed.
     *
     * @param string      $locationId
     * @param null|string $maxId      Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LocationFeedResponse
     */
    public function getLocationFeed(
        $locationId,
        $maxId = null)
    {
        $locationFeed = $this->request("feed/location/{$locationId}/");
        if (!is_null($maxId)) {
            $locationFeed->addParams('max_id', $maxId);
        }

        return $locationFeed->getResponse(new Response\LocationFeedResponse());
    }

    /**
     * Get popular feed.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PopularFeedResponse
     */
    public function getPopularFeed()
    {
        return $this->request('feed/popular/')
        ->addParams('people_teaser_supported', '1')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', 'true')
        ->getResponse(new Response\PopularFeedResponse());
    }

    /**
     * Get hashtag feed.
     *
     * @param string      $hashtagString Hashtag string, not including the "#".
     * @param null|string $maxId         Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\TagFeedResponse
     */
    public function getHashtagFeed(
        $hashtagString,
        $maxId = null)
    {
        $hashtagFeed = $this->request("feed/tag/{$hashtagString}/");
        if (!is_null($maxId)) {
            $hashtagFeed->addParams('max_id', $maxId);
        }

        return $hashtagFeed->getResponse(new Response\TagFeedResponse());
    }

    /**
     * Get list of who a user is following.
     *
     * @param string      $userId Numerical UserPK ID.
     * @param null|string $maxId  Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FollowerAndFollowingResponse
     */
    public function getUserFollowings(
        $userId,
        $maxId = null)
    {
        $requestData = $this->request("friendships/{$userId}/following/")
        ->addParams('rank_token', $this->rank_token);
        if (!is_null($maxId)) {
            $requestData->addParams('max_id', $maxId);
        }

        return $requestData->getResponse(new Response\FollowerAndFollowingResponse());
    }

    /**
     * Get list of who a user is followed by.
     *
     * @param string      $userId Numerical UserPK ID.
     * @param null|string $maxId  Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FollowerAndFollowingResponse
     */
    public function getUserFollowers(
        $userId,
        $maxId = null)
    {
        $requestData = $this->request("friendships/{$userId}/followers/")
        ->addParams('rank_token', $this->rank_token);
        if (!is_null($maxId)) {
            $requestData->addParams('max_id', $maxId);
        }

        return $requestData->getResponse(new Response\FollowerAndFollowingResponse());
    }

    /**
     * Get list of who you are following.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FollowerAndFollowingResponse
     */
    public function getSelfUsersFollowing(
        $maxId = null)
    {
        return $this->getUserFollowings($this->username_id, $maxId);
    }

    /**
     * Get list of your own followers.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FollowerAndFollowingResponse
     */
    public function getSelfUserFollowers(
        $maxId = null)
    {
        return $this->getUserFollowers($this->username_id, $maxId);
    }

    /**
     * Get list of pending friendship requests
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FollowerAndFollowingResponse
     */
    public function getPendingFriendshipRequets()
    {
        $requestData = $this->request("friendships/pending/");
        return $requestData->getResponse(new Response\FollowerAndFollowingResponse());
    }

    /**
     * Like a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function like(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/like/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Unlike a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function unlike(
        $mediaId)
    {
        return $this->request("media/{$mediaId}/unlike/")
         ->addPost('_uuid', $this->uuid)
         ->addPost('_uid', $this->username_id)
         ->addPost('_csrftoken', $this->token)
         ->addPost('media_id', $mediaId)
         ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Get media comments.
     *
     * @param string      $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param null|string $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaCommentsResponse
     */
    public function getMediaComments(
        $mediaId,
        $maxId = null)
    {
        return $this->request("media/{$mediaId}/comments/")
        ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION)
        ->addParams('max_id', $maxId)
        ->getResponse(new Response\MediaCommentsResponse());
    }

    /**
     * Set your account's first name and phone (optional).
     *
     * @param string $name  Your first name.
     * @param string $phone Your phone number (optional).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function setNameAndPhone(
        $name = '',
        $phone = '')
    {
        return $this->request('accounts/set_phone_and_name/')
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('first_name', $name)
        ->addPost('phone_number', $phone)
        ->getResponse(new \InstagramAPI\Response());
    }

    /**
     * Get direct share inbox.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DirectShareInboxResponse
     */
    public function getDirectShare()
    {
        return $this->request('direct_share/inbox/?')
        ->getResponse(new Response\DirectShareInboxResponse());
    }

    /**
     * Backup all of your own uploaded photos and videos. :).
     *
     * @param string $baseOutputPath (optional) Base-folder for output.
     *                               Uses "backups/" path in lib dir if null.
     * @param bool   $printProgress  (optional) Toggles terminal output.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function backup(
        $baseOutputPath = null,
        $printProgress = true)
    {
        // Decide which path to use.
        if ($baseOutputPath === null) {
            $baseOutputPath = Constants::SRC_DIR.'/../backups/';
        }

        // Recursively create output folders for the current backup.
        $backupFolder = $baseOutputPath.$this->username.'/'.date('Y-m-d').'/';
        if (!is_dir($backupFolder)) {
            mkdir($backupFolder, 0755, true);
        }

        // Download all media to the output folders.
        $nextMaxId = null;
        do {
            $myTimeline = $this->getSelfUserFeed($nextMaxId);

            foreach ($myTimeline->getItems() as $item) {
                if ($item->media_type == Response\Model\Item::PHOTO) {
                    $itemUrl = $item->getImageVersions2()->candidates[0]->getUrl();
                } else {
                    $itemUrl = $item->getVideoVersions()[0]->getUrl();
                }
                $fileExtension = pathinfo(parse_url($itemUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                $filePath = $backupFolder.$item->getId().'.'.$fileExtension;
                if ($printProgress) {
                    echo sprintf("* Downloading \"%s\" to \"%s\".\n", $itemUrl, $filePath);
                }
                copy($itemUrl, $filePath);
            }
        } while (!is_null($nextMaxId = $myTimeline->getNextMaxId()));
    }

    /**
     * Follow.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     */
    public function follow(
        $userId)
    {
        return $this->request("friendships/create/{$userId}/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Unfollow.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     */
    public function unfollow(
        $userId)
    {
        return $this->request("friendships/destroy/{$userId}/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Report media.
     *
     * @param string $exploreSourceToken Token related to the media.
     * @param string $userId             Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReportResponse
     */
    public function reportMedia(
        $exploreSourceToken,
        $userId)
    {
        return $this->request('discover/explore_report/')
        ->addParam('explore_source_token', $exploreSourceToken)
        ->addParam('m_pk', $this->$username_id)
        ->addParam('a_pk', $userId)
        ->getResponse(new Response\ReportResponse());
    }

    /**
     * Get suggested users.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SuggestedUsersResponse
     */
    public function getSuggestedUsers(
        $userId)
    {
        return $this->request('discover/chaining/')
        ->addParams('target_id', $userId)
        ->getResponse(new Response\SuggestedUsersResponse());
    }

    /**
     * Block a user.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     */
    public function block(
        $userId)
    {
        return $this->request("friendships/block/{$userId}/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Unblock a user.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     */
    public function unblock(
        $userId)
    {
        return $this->request("friendships/unblock/{$userId}/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Block a user from viewing your story.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     */
    public function blockFriendStory(
        $userId)
    {
        return $this->request("friendships/block_friend_reel/{$userId}/")
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('source', 'profile')
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Unblock a user from viewing your story.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     */
    public function unblockFriendStory(
        $userId)
    {
        return $this->request("friendships/unblock_friend_reel/{$userId}/")
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('source', 'profile')
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Show a user's friendship status with you.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\Model\FriendshipStatus
     */
    public function getUserFriendship(
        $userId)
    {
        return $this->request("friendships/show/{$userId}/")->getResponse(new Response\Model\FriendshipStatus());
    }

    /**
     * Show multiple users' friendship status with you.
     *
     * @param string|string[] $userList List of numerical UserPK IDs.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipsShowManyResponse
     */
    public function getUsersFriendship(
        $userList)
    {
        if (!is_array($userList)) {
            $userList = [$userList];
        }

        return $this->request('friendships/show_many/')
        ->setSignedPost(false)
        ->addPost('_uuid', $this->uuid)
        ->addPost('user_ids', implode(',', $userList))
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new Response\FriendshipsShowManyResponse());
    }

    /**
     * Get feed of your liked media.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LikeFeedResponse
     */
    public function getLikedMedia(
        $maxId = null)
    {
        return $this->request('feed/liked/?'.(!is_null($maxId) ? 'max_id='.$maxId.'&' : ''))
        ->getResponse(new Response\LikeFeedResponse());
    }

    /**
     * Search for tags.
     *
     * @param string $query
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SearchTagResponse
     */
    public function searchTags(
        $query)
    {
        return $this->request('tags/search/')
        ->addParams('is_typeahead', true)
        ->addParams('q', $query)
        ->addParams('rank_token', $this->rank_token)
        ->getResponse(new Response\SearchTagResponse());
    }

    /**
     * Create a custom API request.
     *
     * Used internally, but can also be used by end-users if they want
     * to create completely custom API queries without modifying this library.
     *
     * @param string $url
     *
     * @return \InstagramAPI\Request
     */
    public function request(
        $url)
    {
        return new Request($this, $url);
    }
}
