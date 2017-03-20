<?php

namespace InstagramAPI;

class Instagram
{
    /**
     * Reference to the class instance.
     *
     * @var Instagram
     */
    public static $instance;

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
     * Raw API communication class.
     *
     * @var HttpInterface
     */
    public $http;

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
    public function __construct($debug = false, $truncatedDebug = false, $settingsAdapter = null)
    {
        self::$instance = $this;
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

        $this->http = new HttpInterface($this);
    }

    /**
     * Set the active account for the class instance.
     *
     * You can call this multiple times to switch between multiple accounts.
     *
     * @param string $username Your Instagram username.
     * @param string $password Your Instagram password.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function setUser($username, $password)
    {
        $this->settings = new \InstagramAPI\Settings\Adapter($this->settingsAdapter, $username);

        // Generate the user's Device instance, which will be created from the
        // user's last-used device IF they've got a valid, good one stored.
        // But if they've got a BAD/none, this will create a brand-new device.
        $savedDeviceString = $this->settings->get('devicestring');
        $this->device = new \InstagramAPI\Devices\Device($savedDeviceString);

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
            $this->settings->set('device_id', SignatureUtils::generateDeviceId());
            $this->settings->set('phone_id', SignatureUtils::generateUUID(true));
            $this->settings->set('uuid', SignatureUtils::generateUUID(true));

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

        // Configures HttpInterface for current user AND updates isLoggedIn
        // state if it fails to load the expected cookies from the user's jar.
        // Must be done last here, so that isLoggedIn is properly updated!
        // NOTE: If we generated a new device we start a new cookie jar.
        $this->http->updateFromSettingsAdapter($resetCookieJar);
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
        $this->http->setVerifySSL($state);
    }

    /**
     * Gets the current SSL verification behavior of the HttpInterface.
     *
     * @return bool|string
     */
    public function getVerifySSL()
    {
        return $this->http->getVerifySSL();
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
        $this->http->setProxy($value);
    }

    /**
     * Gets the current proxy used for requests.
     *
     * @return string|array|null
     */
    public function getProxy()
    {
        return $this->http->getProxy();
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
    public function setOutputInterface($value)
    {
        $this->http->setOutputInterface($value);
    }

    /**
     * Gets the current network interface override used for requests.
     *
     * @return string|null
     */
    public function getOutputInterface()
    {
        return $this->http->getOutputInterface();
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
     * @return ExploreResponse
     */
    public function login($forceLogin = false, $appRefreshInterval = 1800)
    {
        // Perform a full relogin if necessary.
        if (!$this->isLoggedIn || $forceLogin) {
            $this->syncFeatures(true);

            $response = $this->request('si/fetch_headers')
            ->requireLogin(true)
            ->addParams('challenge_type', 'signup')
            ->addParams('guid', $this->uuid)
            ->getResponse(new ChallengeResponse(), true);

            $response = $this->request('accounts/login/')
            ->requireLogin(true)
            ->addPost('phone_id', $this->settings->get('phone_id'))
            ->addPost('_csrftoken', $response->getFullResponse()[0])
            ->addPost('username', $this->username)
            ->addPost('guid', $this->uuid)
            ->addPost('device_id', $this->device_id)
            ->addPost('password', $this->password)
            ->addPost('login_attempt_count', 0)
            ->getResponse(new LoginResponse(), true);

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
     * @return LogoutResponse
     */
    public function logout()
    {
        return $this->request('accounts/logout/')->getResponse(new LogoutResponse());
    }

    /**
     * Perform an Instagram "feature synchronization" call.
     *
     * @param bool $prelogin
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return SyncResponse
     */
    public function syncFeatures($prelogin = false)
    {
        if ($prelogin) {
            return $this->request('qe/sync/')
            ->requireLogin(true)
            ->addPost('id', SignatureUtils::generateUUID(true))
            ->addPost('experiments', Constants::LOGIN_EXPERIMENTS)
            ->getResponse(new SyncResponse());
        } else {
            return $this->request('qe/sync/')
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->username_id)
            ->addPost('_csrftoken', $this->token)
            ->addPost('id', $this->username_id)
            ->addPost('experiments', Constants::EXPERIMENTS)
            ->getResponse(new SyncResponse());
        }
    }

    /**
     * Retrieve list of all friends.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return AutoCompleteUserListResponse|null Will be NULL if throttled by Instagram.
     */
    public function getAutoCompleteUserList()
    {
        // NOTE: This is a special, very heavily throttled API endpoint.
        // Instagram REQUIRES that you wait several minutes between calls to it.
        try {
            $request = $this->request('friendships/autocomplete_user_list/')
            ->setCheckStatus(false)
            ->addParams('version', '2');

            return $request->getResponse(new AutoCompleteUserListResponse());
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
    public function pushRegister($gcmToken)
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

        return $this->http->api('push/register/?platform=10&device_type=android_mqtt', SignatureUtils::generateSignature($data))[1];
    }

    /**
     * Get your own timeline feed.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return TimelineFeedResponse
     */
    public function getTimelineFeed($maxId = null)
    {
        $request = $this->request('feed/timeline')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', true);
        if ($maxId) {
            $request->addParams('max_id', $maxId);
        }

        return $request->getResponse(new TimelineFeedResponse());
    }

    /**
     * Get insights.
     *
     * @param $day
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return InsightsResponse
     */
    public function getInsights($day = null)
    {
        if (empty($day)) {
            $day = date('d');
        }
        $request = $this->request('insights/account_organic_insights')
        ->addParams('show_promotions_in_landing_page', 'true')
        ->addParams('first', $day);

        return $request->getResponse(new InsightsResponse());
    }

    /**
     * Get media insights.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return MediaInsightsResponse
     */
    public function getMediaInsights($mediaId)
    {
        $request = $this->request("insights/media_organic_insights/{$mediaId}")
        ->setSignedPost(true)
        ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION);

        return $request->getResponse(new MediaInsightsResponse());
    }

    /**
     * Get megaphone log.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return MegaphoneLogResponse
     */
    protected function getMegaphoneLog()
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
        ->getResponse(new MegaphoneLogResponse());
    }

    /**
     * Get pending inbox data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return PendingInboxResponse
     */
    public function getPendingInbox()
    {
        return $this->request('direct_v2/pending_inbox')->getResponse(new PendingInboxResponse());
    }

    /**
     * Get ranked list of recipients.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return RankedRecipientsResponse
     */
    public function getRankedRecipients()
    {
        return $this->request('direct_v2/ranked_recipients')
        ->addParams('show_threads', true)
        ->getResponse(new RankedRecipientsResponse());
    }

    /**
     * Get recent recipients.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return RecentRecipientsResponse
     */
    public function getRecentRecipients()
    {
        return $this->request('direct_share/recent_recipients/')
        ->getResponse(new RecentRecipientsResponse());
    }

    /**
     * Get Explore tab data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ExploreResponse
     */
    public function getExplore()
    {
        return $this->request('discover/explore/')->getResponse(new ExploreResponse());
    }

    /**
     * Get Home channel data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return DiscoverChannelsResponse
     */
    public function getDiscoverChannels()
    {
        return $this->request('discover/channels_home/')->getResponse(new DiscoverChannelsResponse());
    }

    /**
     * Expose.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ExposeResponse
     */
    public function expose()
    {
        return $this->request('qe/expose/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('id', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('experiment', 'ig_android_profile_contextual_feed')
        ->getResponse(new ExposeResponse());
    }

    /**
     * INTERNAL.
     *
     * @param string $type          What type of upload ("timeline" or "story",
     *                              but not "album". They're handled elsewhere.)
     * @param string $photoFilename The photo filename.
     * @param string $captionText   Caption to use for the photo.
     * @param null   $location      Location (only used for "timeline" photos).
     * @param null   $filter        Photo filter.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ConfigureResponse
     */
    protected function _uploadPhoto($type, $photoFilename, $captionText = null, $location = null, $filter = null)
    {
        // Make sure we don't allow "album" photo uploads via this function.
        if ($type != 'timeline' && $type != 'story') {
            throw new \InvalidArgumentException(sprintf('Unsupported photo upload type "%s".', $type));
        }

        // Perform the upload and then configure it for our timeline/story.
        $upload = $this->http->uploadPhotoData($type, $photoFilename);
        $configure = $this->configure($type, $upload->getUploadId(), $photoFilename, $captionText, $location, $filter);

        return $configure;
    }

    /**
     * Uploads a photo to your Instagram timeline.

     * @param string $photoFilename The photo filename.
     * @param string $captionText   Caption to use for the photo.
     * @param null   $location      Location where photo was taken.
     * @param null   $filter        Photo filter.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ConfigureResponse
     */
    public function uploadTimelinePhoto($photoFilename, $captionText = null, $location = null, $filter = null)
    {
        return $this->_uploadPhoto('timeline', $photoFilename, $captionText, $location, $filter);
    }

    /**
     * Uploads a photo to your Instagram story.
     *
     * @param string $photoFilename The photo filename.
     * @param string $captionText   Caption to display over the story photo.
     * @param null   $filter        Photo filter.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ConfigureResponse
     */
    public function uploadStoryPhoto($photoFilename, $captionText = null, $filter = null)
    {
        return $this->_uploadPhoto('story', $photoFilename, $captionText, null, $filter);
    }

    /**
     * INTERNAL.
     *
     * @param string   $type          What type of upload ("timeline" or "story",
     *                                but not "album". They're handled elsewhere.)
     * @param string   $videoFilename The video filename.
     * @param string   $captionText   Caption to use for the video.
     * @param string   $customThumb   Optional path to custom video thumbnail.
     *                                If nothing provided, we generate from video.
     * @param string[] $userTags      Array of UserPK IDs of people tagged in your video.
     *                                (only used for "story" videos!).
     * @param int      $maxAttempts   Total attempts to upload all chunks before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return ConfigureVideoResponse
     */
    protected function _uploadVideo($type, $videoFilename, $captionText = null, $customThumb = null, $userTags = null, $maxAttempts = 10)
    {
        // Make sure we don't allow "album" video uploads via this function.
        if ($type != 'timeline' && $type != 'story') {
            throw new \InvalidArgumentException(sprintf('Unsupported video upload type "%s".', $type));
        }

        // Request parameters for uploading a new video.
        $uploadParams = $this->http->requestVideoUploadURL();

        // Attempt to upload the video data.
        $upload = $this->http->uploadVideoData($type, $videoFilename, $uploadParams, $maxAttempts);

        // Attempt to upload the thumbnail, associated with our video's ID.
        if (is_null($customThumb)) {
            $this->http->uploadPhotoData($type, $videoFilename, 'videofile', $uploadParams['upload_id']);
        } else {
            $this->http->uploadPhotoData($type, $customThumb, 'photofile', $uploadParams['upload_id']);
        }

        // Configure the uploaded video and attach it to our timeline/story.
        $configure = $this->configureVideoWithRetries($type, $uploadParams['upload_id'], $captionText, $userTags);

        return $configure;
    }

    /**
     * Uploads a video to your Instagram timeline.
     *
     * @param string $videoFilename The video filename.
     * @param string $captionText   Caption to use for the video.
     * @param string $customThumb   Optional path to custom video thumbnail.
     *                              If nothing provided, we generate from video.
     * @param int    $maxAttempts   Total attempts to upload all chunks before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return ConfigureVideoResponse
     */
    public function uploadTimelineVideo($videoFilename, $captionText = null, $customThumb = null, $maxAttempts = 10)
    {
        return $this->_uploadVideo('timeline', $videoFilename, $captionText, $customThumb, null, $maxAttempts);
    }

    /**
     * Uploads a video to your Instagram story.
     *
     * @param string   $videoFilename The video filename.
     * @param string   $captionText   Caption to use for the video.
     * @param string   $customThumb   Optional path to custom video thumbnail.
     *                                If nothing provided, we generate from video.
     * @param string[] $userTags      Array of UserPK IDs of people tagged in your video.
     * @param int      $maxAttempts   Total attempts to upload all chunks before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return ConfigureVideoResponse
     */
    public function uploadStoryVideo($videoFilename, $captionText = null, $customThumb = null, $userTags = null, $maxAttempts = 10)
    {
        return $this->_uploadVideo('story', $videoFilename, $captionText, $customThumb, $userTags, $maxAttempts);
    }

    /**
     * Uploads an album to your Instagram timeline.
     *
     * An album is also known as a "carousel" and "sidecar". They can contain up
     * to 10 photos or videos (at the moment).
     *
     * @param array $media       Array of image/video metadata (type, file, usertags etc)
     *                           You can only provide "usertags" for PHOTOS!
     * @param null  $captionText Text for album
     * @param null  $location    Geotag
     * @param null  $filter
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return ConfigureResponse
     */
    public function uploadTimelineAlbum($media, $captionText = null, $location = null, $filter = null)
    {
        if (empty($media)) {
            throw new \InvalidArgumentException("List of media to upload can't be empty.");
        }

        $hasUploadedVideo = false;
        foreach ($media as $key => $item) {
            if (!file_exists($item['file'])) {
                throw new \InvalidArgumentException(sprintf('File "%s" does not exist.', $item['file']));
            }

            switch ($item['type']) {
            case 'photo':
                $result = $this->http->uploadPhotoData('album', $item['file']);
                $media[$key]['upload_id'] = $result->getUploadId();
                break;
            case 'video':
                $hasUploadedVideo = true;

                // Request parameters for uploading a new video.
                $uploadParams = $this->http->requestVideoUploadURL();
                $media[$key]['upload_id'] = $uploadParams['upload_id'];

                // Attempt to upload the video data.
                // TODO: Consider adding the final "maxAttempts" parameter and
                // making it configurable in uploadTimelineAlbum's parameters. But first
                // finalize the behavior of uploadTimelineAlbum (we may have to
                // remove the "filter" parameter and making it part of the
                // per-photo configuration array, for example, if Instagram
                // allows per-photo filters inside of albums).
                $this->http->uploadVideoData('album', $item['file'], $uploadParams);

                // Attempt to upload the thumbnail, associated with our video's ID.
                $this->http->uploadPhotoData('album', $item['file'], 'videofile', $uploadParams['upload_id']);

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

                if (isset($item['usertags'])) {
                    $photoConfig['usertags'] = json_encode(['in' => $item['usertags']]);
                }

                $uploadRequests[] = $photoConfig;
                break;
            case 'video':
                $videoConfig = [
                    //'length'              => 0.00,
                    'date_time_original'  => $date,
                    'scene_type'          => 1,
                    'poster_frame_index'  => 0,
                    'trim_type'           => 0,
                    'disable_comments'    => false,
                    'upload_id'           => $item['upload_id'],
                    'source_type'         => 'library',
                    'geotag_enabled'      => false,
                    'edits', [
                        //'length'          => 0.00,
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
        $configure = $this->configure('album', $uploadRequests, $item['file'], $captionText, $location, $filter);

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
     * @return Response
     */
    public function directShare($recipients, $mediaId, $text = null)
    {
        return $this->http->directShare(
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
     * @return Response
     */
    public function directMessage($recipients, $text)
    {
        return $this->http->directShare(
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
     * @return Response
     */
    public function directPhoto($recipients, $photoFilename, $text = null)
    {
        return $this->http->directShare(
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
     * @return DirectThreadResponse
     */
    public function directThread($threadId, $cursorId = null)
    {
        $request = $this->request("direct_v2/threads/$threadId/");
        if ($cursorId !== null) {
            $request->addParams('cursor', $cursorId);
        }

        return $request->getResponse(new DirectThreadResponse());
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
    public function directThreadAction($threadId, $threadAction)
    {
        $data = json_encode([
            '_uuid'      => $this->uuid,
            '_uid'       => $this->username_id,
            '_csrftoken' => $this->token,
        ]);

        return $this->http->api("direct_v2/threads/{$threadId}/{$threadAction}/", SignatureUtils::generateSignature($data))[1];
    }

    /**
     * Helper function for reliably configuring videos.
     *
     * Exactly the same as configureVideo() but performs multiple attempts. Very
     * useful since Instagram sometimes can't configure a newly uploaded video
     * file until a few seconds have passed.
     *
     * @param string   $type        What type of upload ("timeline" or "story",
     *                              but not "album". They're handled elsewhere.)
     * @param string   $upload_id   The ID of the upload to configure.
     * @param string   $captionText Caption to use for the video.
     * @param string[] $userTags    Array of UserPK IDs of people tagged in your video.
     *                              (only used for "story" videos!).
     * @param int      $maxAttempts Total attempts to configure video before throwing.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ConfigureVideoResponse
     *
     * @see configureVideo()
     */
    public function configureVideoWithRetries($type, $upload_id, $captionText = null, $userTags = null, $maxAttempts = 5)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            try {
                // Attempt to configure video parameters.
                $configure = $this->configureVideo($type, $upload_id, $captionText, $userTags);
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
     * @param string   $type        What type of upload ("timeline" or "story",
     *                              but not "album". They're handled elsewhere.)
     * @param string   $upload_id   The ID of the upload to configure.
     * @param string   $captionText Caption to use for the video.
     * @param string[] $userTags    Array of UserPK IDs of people tagged in your video.
     *                              (only used for "story" videos!).
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ConfigureVideoResponse
     */
    public function configureVideo($type, $upload_id, $captionText = null, $userTags = null)
    {
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
        ->addPost('configure_mode', 1)
        ->addPost('video_result', 'deprecated')
        ->addPost('upload_id', $upload_id)
        ->addPost('source_type', 4)
        // TODO
        //->addPost('length', number_format(0.00, 2, '.', ''))
        ->addPost('length', 0)
        ->addPost('date_time_original', time())
        ->addPost('filter_type', 0)
        ->addPost('video_result', 'deprecated')
        ->addPost('device',
            [
                'manufacturer'      => $this->device->getManufacturer(),
                'model'             => $this->device->getModel(),
                'android_version'   => $this->device->getAndroidVersion(),
                'android_release'   => $this->device->getAndroidRelease(),
            ])
            /* TODO
        ->addPost('clips', [
            'length'   => number_format(0.00, 2, '.', ''),
            'source_type'   => 4,
        ])*/
        ->addPost('_csrftoken', $this->token)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->setReplacePost(['"length":0' => '"length":0.00']);

        if ($captionText !== '' && !is_null($captionText) && $captionText) {
            $requestData->addPost('caption', $captionText);
        }

        // TODO: IMPLEMENT THIS "STORY USER TAGS" FEATURE!
        // Reel Mention example --> build with user id
        // [{\"y\":0.3407772676161919,\"rotation\":0,\"user_id\":\"USER_ID\",\"x\":0.39892578125,\"width\":0.5619921875,\"height\":0.06011525487256372}]
        if ($type == 'story') {
            $requestData->addPost('story_media_creation_date', time());
            if (!is_null($userTags)) {
                //$requestData->addPost('reel_mentions', $userTags)
            }
        }

        $configure = $requestData->getResponse(new ConfigureVideoResponse());

        return $configure;
    }

    /**
     * Configure uploaded media parameters (primarily for photos, but also albums).
     *
     * @param string $type          What type of entry ("timeline", "story" or "album").
     * @param string $upload_id     The ID of the entry to configure.
     * @param string $photoFilename The photo filename.
     * @param string $captionText
     * @param null   $location
     * @param null   $filter
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ConfigureResponse
     */
    public function configure($type, $upload_id, $photoFilename, $captionText = null, $location = null, $filter = null)
    {
        $size = getimagesize($photoFilename)[0];
        if (is_null($captionText)) {
            $captionText = '';
        }

        if ($type == 'album') {
            $endpoint = 'media/configure_sidecar/?';
        } elseif ($type == 'story') {
            $endpoint = 'media/configure_to_story/';
        } else {
            $endpoint = 'media/configure/';
        }

        $requestData = $this->request($endpoint)
        ->addPost('_csrftoken', $this->token)
        ->addPost('client_shared_at', time())
        ->addPost('source_type', 3)
        ->addPost('configure_mode', 1)
        ->addPost('_uid', $this->username_id)
        ->addPost('_uuid', $this->uuid)
        ->addPost('caption', $captionText)
        ->addPost('client_timestamp', time())
        ->addPost('device',
            [
                'manufacturer'      => $this->device->getManufacturer(),
                'model'             => $this->device->getModel(),
                'android_version'   => $this->device->getAndroidVersion(),
                'android_release'   => $this->device->getAndroidRelease(),
            ]
        );

        if ($type == 'album') {
            $requestData->addPost('client_sidecar_id', Utils::generateUploadId())
            ->addPost('children_metadata', $upload_id);
        } else {
            $requestData->addPost('upload_id', $upload_id);
        }

        if (!is_null($location)) {
            $loc = [
                $location->getExternalIdSource().'_id'   => $location->getExternalId(),
                'name'                                   => $location->getName(),
                'lat'                                    => $location->getLat(),
                'lng'                                    => $location->getLng(),
                'address'                                => $location->getAddress(),
                'external_source'                        => $location->getExternalIdSource(),
            ];

            $requestData->addPost('location', json_encode($loc))
            ->addPost('geotag_enabled', true)
            ->addPost('media_latitude', $location->getLat())
            ->addPost('posting_latitude', $location->getLat())
            ->addPost('media_longitude', $location->getLng())
            ->addPost('posting_longitude', $location->getLng())
            ->addPost('altitude', mt_rand(10, 800));
        }

        if (!is_null($filter)) {
            $requestData->addPost('edits', ['filter_type' => Utils::getFilterCode($filter)]);
        }

        $configure = $requestData->setReplacePost([
            '"crop_center":[0,0]'                       => '"crop_center":[0.0,-0.0]',
            '"crop_zoom":1'                             => '"crop_zoom":1.0',
            '"crop_original_size":'."[{$size},{$size}]" => '"crop_original_size":'."[{$size}.0,{$size}.0]",
        ])
        ->getResponse(new ConfigureResponse());

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
     * @return EditMediaResponse
     */
    public function editMedia($mediaId, $captionText = '', $usertags = null)
    {
        if (is_null($usertags)) {
            return $this->request("media/{$mediaId}/edit_media/")
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->username_id)
            ->addPost('_csrftoken', $this->token)
            ->addPost('caption_text', $captionText)
            ->getResponse(new EditMediaResponse());
        } else {
            return $this->request("media/{$mediaId}/edit_media/")
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->username_id)
            ->addPost('_csrftoken', $this->token)
            ->addPost('caption_text', $captionText)
            ->addPost('usertags', $usertags)
            ->getResponse(new EditMediaResponse());
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
     * @return EditMediaResponse
     */
    public function tagUser($mediaId, $userId, $position, $captionText = '')
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
     * @return EditMediaResponse
     */
    public function untagUser($mediaId, $userId, $captionText = '')
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
     * @return SaveAndUnsaveMedia
     */
    public function saveMedia($mediaId)
    {
        return $this->request("media/{$mediaId}/save/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(true)
        ->getResponse(new SaveAndUnsaveMedia());
    }

    /**
     * Unsave a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return SaveAndUnsaveMedia
     */
    public function unsaveMedia($mediaId)
    {
        return $this->request("media/{$mediaId}/unsave/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(true)
        ->getResponse(new SaveAndUnsaveMedia());
    }

    /**
     * Get saved media items feed.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return SavedFeedResponse
     */
    public function getSavedFeed()
    {
        return $this->request('feed/saved/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(true)
        ->getResponse(new SavedFeedResponse());
    }

    /**
     * Remove yourself from a tagged media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return MediaResponse
     */
    public function removeSelfTag($mediaId)
    {
        return $this->request("usertags/{$mediaId}/remove/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new MediaResponse());
    }

    /**
     * Get detailed media information.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return MediaInfoResponse
     */
    public function getMediaInfo($mediaId)
    {
        return $this->request("media/{$mediaId}/info/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new MediaInfoResponse());
    }

    /**
     * Delete a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return MediaDeleteResponse
     */
    public function deleteMedia($mediaId)
    {
        return $this->request("media/{$mediaId}/delete/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new MediaDeleteResponse());
    }

    /**
     * Disable comments for a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response
     */
    public function disableMediaComments($mediaId)
    {
        return $this->request("media/{$mediaId}/disable_comments/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(false)
        ->getResponse(new Response());
    }

    /**
     * Enable comments for a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response
     */
    public function enableMediaComments($mediaId)
    {
        return $this->request("media/{$mediaId}/enable_comments/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(false)
        ->getResponse(new Response());
    }

    /**
     * Post a comment on a media item.
     *
     * @param string $mediaId     The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentText Your comment text.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return CommentResponse
     */
    public function comment($mediaId, $commentText)
    {
        return $this->request("media/{$mediaId}/comment/")
        ->addPost('user_breadcrumb', Utils::generateUserBreadcrumb(mb_strlen($commentText)))
        ->addPost('idempotence_token', SignatureUtils::generateUUID(true))
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('comment_text', $commentText)
        ->addPost('containermodule', 'comments_feed_timeline')
        ->getResponse(new CommentResponse());
    }

    /**
     * Delete a comment.
     *
     * @param string $mediaId   The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return DeleteCommentResponse
     */
    public function deleteComment($mediaId, $commentId)
    {
        return $this->request("media/{$mediaId}/comment/{$commentId}/delete/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new DeleteCommentResponse());
    }

    /**
     * Delete multiple comments.
     *
     * @param string $mediaId    The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param string $commentIds List of comment IDs to delete.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return DeleteCommentResponse
     */
    public function deleteComments($mediaId, $commentIds)
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
        ->getResponse(new DeleteCommentResponse());
    }

    /**
     * Like a comment.
     *
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return CommentLikeUnlikeResponse
     */
    public function likeComment($commentId)
    {
        return $this->request("media/{$commentId}/comment_like/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new CommentLikeUnlikeResponse());
    }

    /**
     * Unlike a comment.
     *
     * @param string $commentId The comment's ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return CommentLikeUnlikeResponse
     */
    public function unlikeComment($commentId)
    {
        return $this->request("media/{$commentId}/comment_unlike/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new CommentLikeUnlikeResponse());
    }

    /**
     * Changes your account's profile picture.
     *
     * @param string $photoFilename The photo filename.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return User
     */
    public function changeProfilePicture($photoFilename)
    {
        return $this->http->changeProfilePicture($photoFilename);
    }

    /**
     * Remove your account's profile picture.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UsernameInfoResponse
     */
    public function removeProfilePicture()
    {
        return $this->request('accounts/remove_profile_picture/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new UsernameInfoResponse());
    }

    /**
     * Sets your account to private.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UsernameInfoResponse
     */
    public function setPrivateAccount()
    {
        return $this->request('accounts/set_private/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new UsernameInfoResponse());
    }

    /**
     * Sets your account to public.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UsernameInfoResponse
     */
    public function setPublicAccount()
    {
        return $this->request('accounts/set_public/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new UsernameInfoResponse());
    }

    /**
     * Get details about the currently logged in account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UsernameInfoResponse
     */
    public function getCurrentUser()
    {
        return $this->request('accounts/current_user/')
        ->addParams('edit', true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new UsernameInfoResponse());
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
     * @return UsernameInfoResponse
     */
    public function editProfile($url, $phone, $firstName, $biography, $email, $gender)
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
        ->getResponse(new UsernameInfoResponse());
    }

    /**
     * Change your account's password.
     *
     * @param string $oldPassword Old password.
     * @param string $newPassword New password.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ChangePasswordResponse
     */
    public function changePassword($oldPassword, $newPassword)
    {
        return $this->request('accounts/change_password/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('old_password', $oldPassword)
        ->addPost('new_password1', $newPassword)
        ->addPost('new_password2', $newPassword)
        ->getResponse(new ChangePasswordResponse());
    }

    /**
     * Get recent activity.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ActivityNewsResponse
     */
    public function getRecentActivity()
    {
        return $this->request('news/inbox/')->addParams('activity_module', 'all')->getResponse(new ActivityNewsResponse());
    }

    /**
     * Get news feed with recent activity from all accounts you follow.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FollowingRecentActivityResponse
     */
    public function getFollowingRecentActivity($maxId = null)
    {
        $activity = $this->request('news/');
        if (!is_null($maxId)) {
            $activity->addParams('max_id', $maxId);
        }

        return $activity->getResponse(new FollowingRecentActivityResponse());
    }

    /**
     * Get direct inbox messages for your account.
     *
     * @param string|null $cursorId
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return V2InboxResponse
     */
    public function getV2Inbox($cursorId = null)
    {
        $request = $this->request('direct_v2/inbox/');
        if ($cursorId !== null) {
            $request->addParams('cursor', $cursorId);
        }

        return $request->getResponse(new V2InboxResponse());
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
     * @return UsertagsResponse
     */
    public function getUserTags($userId, $maxId = null, $minTimestamp = null)
    {
        return $this->request("usertags/{$userId}/feed/")
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', 'true')
        ->addParams('max_id', (!is_null($maxId) ? $maxId : ''))
        ->addParams('min_timestamp', (!is_null($minTimestamp) ? $minTimestamp : ''))
        ->getResponse(new UsertagsResponse());
    }

    /**
     * Get user taggings for your own account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UsertagsResponse
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
     * @return MediaLikersResponse
     */
    public function getMediaLikers($mediaId)
    {
        return $this->request("media/{$mediaId}/likers/")->getResponse(new MediaLikersResponse());
    }

    /**
     * Facebook user search.
     *
     * @param string $query
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FBSearchResponse
     */
    public function searchFBUsers($query)
    {
        return $this->request('fbsearch/topsearch/')
        ->addParams('context', 'blended')
        ->addParams('query', $query)
        ->addParams('rank_token', $this->rank_token)
        ->getResponse(new FBSearchResponse());
    }

    /**
     * Search for Instagram users.
     *
     * @param string $query
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return SearchUserResponse
     */
    public function searchUsers($query)
    {
        return $this->request('users/search/')
        ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION)
        ->addParams('is_typeahead', true)
        ->addParams('query', $query)
        ->addParams('rank_token', $this->rank_token)
        ->getResponse(new SearchUserResponse());
    }

    /**
     * Search for users via address book.
     *
     * @param array $contacts
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return AddressBookResponse
     */
    public function searchInAddressBook($contacts)
    {
        return $this->request('address_book/link/?include=extra_display_name,thumbnails')
            ->setSignedPost(false)
            ->addPost('contacts', json_encode($contacts, true))
            ->getResponse(new AddressBookResponse());
    }

    /**
     * Get details about a specific user via their username.
     *
     * @param string $username Username as string (NOT as a numerical ID).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UsernameInfoResponse
     */
    public function getUserInfoByName($username)
    {
        return $this->request("users/{$username}/usernameinfo/")->getResponse(new UsernameInfoResponse());
    }

    /**
     * Get details about a specific user via their UserPK ID.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UsernameInfoResponse
     */
    public function getUserInfoById($userId)
    {
        return $this->request("users/{$userId}/info/")->getResponse(new UsernameInfoResponse());
    }

    /**
     * Get user details about your own account.
     *
     * Also try getCurrentUser() instead, for even more details.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UsernameInfoResponse
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
    public function getUsernameId($username)
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
     * @return TagRelatedResponse
     */
    public function getTagRelated($tag)
    {
        return $this->request("tags/{$tag}/related")
        ->addParams('visited', '[{"id":"'.$tag.'","type":"hashtag"}]')
        ->addParams('related_types', '["hashtag"]')
        ->getResponse(new TagRelatedResponse());
    }

    /**
     * Get detailed tag information.
     *
     * @param string $tag
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return TagInfoResponse
     */
    public function getTagInfo($tag)
    {
        return $this->request("tags/{$tag}/info")
        ->getResponse(new TagInfoResponse());
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
     * @return ReelsTrayFeedResponse
     *
     * @see getUserStoryFeed()
     */
    public function getReelsTrayFeed()
    {
        return $this->request('feed/reels_tray/')->getResponse(new ReelsTrayFeedResponse());
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
     * @return Reel
     *
     * @see getUserStoryFeed()
     */
    public function getUserReelMediaFeed($userId)
    {
        return $this->request("feed/user/{$userId}/reel_media/")
        ->getResponse(new Reel());
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
     * @return UserStoryFeedResponse
     *
     * @see getUserReelMediaFeed()
     */
    public function getUserStoryFeed($userId)
    {
        return $this->request("feed/user/{$userId}/story/")
        ->getResponse(new UserStoryFeedResponse());
    }

    /**
     * Get multiple users' story feeds at once.
     *
     * @param string|string[] $userList List of numerical UserPK IDs.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return ReelsMediaResponse
     */
    public function getReelsMediaFeed($userList)
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
        ->getResponse(new ReelsMediaResponse());
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
     * @return UserFeedResponse
     */
    public function getUserFeed($userId, $maxId = null, $minTimestamp = null)
    {
        return $this->request("feed/user/{$userId}/")
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', 'true')
        ->addParams('max_id', (!is_null($maxId) ? $maxId : ''))
        ->addParams('min_timestamp', (!is_null($minTimestamp) ? $minTimestamp : ''))
        ->getResponse(new UserFeedResponse());
    }

    /**
     * Get your own timeline feed.
     *
     * @param null|string $maxId        Next "maximum ID", used for pagination.
     * @param null|int    $minTimestamp Minimum timestamp.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return UserFeedResponse
     */
    public function getSelfUserFeed($maxId = null, $minTimestamp = null)
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
     * @return GeoMediaResponse
     *
     * @see getUserFeed()
     */
    public function getGeoMedia($userId)
    {
        return $this->request("maps/user/{$userId}/")->getResponse(new GeoMediaResponse());
    }

    /**
     * Get location based media feed for your own account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return GeoMediaResponse
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
     * @return LocationResponse
     */
    public function searchLocation($latitude, $longitude, $query = null)
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

        return $locations->getResponse(new LocationResponse());
    }

    /**
     * Search for Facebook locations by name.
     *
     * @param string $query
     * @param int    $count (optional) Facebook will return up to this many results.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FBLocationResponse
     */
    public function searchFBLocation($query, $count = null)
    {
        $location = $this->request('fbsearch/places/')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('query', $query);

        if (!is_null($count)) {
            $location->addParams('count', $count);
        }

        return $location->getResponse(new FBLocationResponse());
    }

    /**
     * Search for Facebook locations by geographical location.
     *
     * @param string $lat Latitude.
     * @param string $lng Longitude.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FBLocationResponse
     */
    public function searchFBLocationByPoint($lat, $lng)
    {
        return $this->request('fbsearch/places/')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('lat', $lat)
        ->addParams('lng', $lng)
        ->getResponse(new FBLocationResponse());
    }

    /**
     * Get location feed.
     *
     * @param string      $locationId
     * @param null|string $maxId      Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return LocationFeedResponse
     */
    public function getLocationFeed($locationId, $maxId = null)
    {
        $locationFeed = $this->request("feed/location/{$locationId}/");
        if (!is_null($maxId)) {
            $locationFeed->addParams('max_id', $maxId);
        }

        return $locationFeed->getResponse(new LocationFeedResponse());
    }

    /**
     * Get popular feed.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return PopularFeedResponse
     */
    public function getPopularFeed()
    {
        return $this->request('feed/popular/')
        ->addParams('people_teaser_supported', '1')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', 'true')
        ->getResponse(new PopularFeedResponse());
    }

    /**
     * Get hashtag feed.
     *
     * @param string      $hashtagString Hashtag string, not including the "#".
     * @param null|string $maxId         Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return TagFeedResponse
     */
    public function getHashtagFeed($hashtagString, $maxId = null)
    {
        $hashtagFeed = $this->request("feed/tag/{$hashtagString}/");
        if (!is_null($maxId)) {
            $hashtagFeed->addParams('max_id', $maxId);
        }

        return $hashtagFeed->getResponse(new TagFeedResponse());
    }

    /**
     * Get list of who a user is following.
     *
     * @param string      $userId Numerical UserPK ID.
     * @param null|string $maxId  Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FollowerAndFollowingResponse
     */
    public function getUserFollowings($userId, $maxId = null)
    {
        $requestData = $this->request("friendships/{$userId}/following/")
        ->addParams('rank_token', $this->rank_token);
        if (!is_null($maxId)) {
            $requestData->addParams('max_id', $maxId);
        }

        return $requestData->getResponse(new FollowerAndFollowingResponse());
    }

    /**
     * Get list of who a user is followed by.
     *
     * @param string      $userId Numerical UserPK ID.
     * @param null|string $maxId  Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FollowerAndFollowingResponse
     */
    public function getUserFollowers($userId, $maxId = null)
    {
        $requestData = $this->request("friendships/{$userId}/followers/")
        ->addParams('rank_token', $this->rank_token);
        if (!is_null($maxId)) {
            $requestData->addParams('max_id', $maxId);
        }

        return $requestData->getResponse(new FollowerAndFollowingResponse());
    }

    /**
     * Get list of who you are following.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FollowerAndFollowingResponse
     */
    public function getSelfUsersFollowing($maxId = null)
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
     * @return FollowerAndFollowingResponse
     */
    public function getSelfUserFollowers($maxId = null)
    {
        return $this->getUserFollowers($this->username_id, $maxId);
    }

    /**
     * Like a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response
     */
    public function like($mediaId)
    {
        return $this->request("media/{$mediaId}/like/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new Response());
    }

    /**
     * Unlike a media item.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response
     */
    public function unlike($mediaId)
    {
        return $this->request("media/{$mediaId}/unlike/")
         ->addPost('_uuid', $this->uuid)
         ->addPost('_uid', $this->username_id)
         ->addPost('_csrftoken', $this->token)
         ->addPost('media_id', $mediaId)
         ->getResponse(new Response());
    }

    /**
     * Get media comments.
     *
     * @param string      $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     * @param null|string $maxId   Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return MediaCommentsResponse
     */
    public function getMediaComments($mediaId, $maxId = null)
    {
        return $this->request("media/{$mediaId}/comments/")
        ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION)
        ->addParams('max_id', $maxId)
        ->getResponse(new MediaCommentsResponse());
    }

    /**
     * Set your account's first name and phone (optional).
     *
     * @param string $name  Your first name.
     * @param string $phone Your phone number (optional).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response
     */
    public function setNameAndPhone($name = '', $phone = '')
    {
        return $this->request('accounts/set_phone_and_name/')
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('first_name', $name)
        ->addPost('phone_number', $phone)
        ->getResponse(new Response());
    }

    /**
     * Get direct share inbox.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return DirectShareInboxResponse
     */
    public function getDirectShare()
    {
        return $this->request('direct_share/inbox/?')
        ->getResponse(new DirectShareInboxResponse());
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
    public function backup($baseOutputPath = null, $printProgress = true)
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
                if ($item->media_type == Item::PHOTO) {
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
     * @return FriendshipResponse
     */
    public function follow($userId)
    {
        return $this->request("friendships/create/{$userId}/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new FriendshipResponse());
    }

    /**
     * Unfollow.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FriendshipResponse
     */
    public function unfollow($userId)
    {
        return $this->request("friendships/destroy/{$userId}/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new FriendshipResponse());
    }

    /**
     * Get suggested users.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return SuggestedUsersResponse
     */
    public function getSuggestedUsers($userId)
    {
        return $this->request('discover/chaining/')
        ->addParams('target_id', $userId)
        ->getResponse(new SuggestedUsersResponse());
    }

    /**
     * Block a user.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FriendshipResponse
     */
    public function block($userId)
    {
        return $this->request("friendships/block/{$userId}/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new FriendshipResponse());
    }

    /**
     * Unblock a user.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FriendshipResponse
     */
    public function unblock($userId)
    {
        return $this->request("friendships/unblock/{$userId}/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new FriendshipResponse());
    }

    /**
     * Show a user's friendship status with you.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FriendshipStatus
     */
    public function getUserFriendship($userId)
    {
        return $this->request("friendships/show/{$userId}/")->getResponse(new FriendshipStatus());
    }

    /**
     * Show multiple users' friendship status with you.
     *
     * @param string|string[] $userList List of numerical UserPK IDs.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return FriendshipsShowManyResponse
     */
    public function getUsersFriendship($userList)
    {
        if (!is_array($userList)) {
            $userList = [$userList];
        }

        return $this->request('friendships/show_many/')
        ->setSignedPost(false)
        ->addPost('_uuid', $this->uuid)
        ->addPost('user_ids', implode(',', $userList))
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new FriendshipsShowManyResponse());
    }

    /**
     * Get feed of your liked media.
     *
     * @param null|string $maxId Next "maximum ID", used for pagination.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return LikeFeedResponse
     */
    public function getLikedMedia($maxId = null)
    {
        return $this->request('feed/liked/?'.(!is_null($maxId) ? 'max_id='.$maxId.'&' : ''))
        ->getResponse(new LikeFeedResponse());
    }

    /**
     * Search for tags.
     *
     * @param string $query
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return SearchTagResponse
     */
    public function searchTags($query)
    {
        return $this->request('tags/search/')
        ->addParams('is_typeahead', true)
        ->addParams('q', $query)
        ->addParams('rank_token', $this->rank_token)
        ->getResponse(new SearchTagResponse());
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
    public function request($url)
    {
        return new Request($url);
    }

    /**
     * Get a reference to the class instance.
     *
     * @return \InstagramAPI\Instagram
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}