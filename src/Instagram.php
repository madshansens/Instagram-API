<?php

namespace InstagramAPI;

/**
 * Instagram's Private API v2.
 *
 * TERMS OF USE:
 * - This code is in no way affiliated with, authorized, maintained, sponsored
 *   or endorsed by Instagram or any of its affiliates or subsidiaries. This is
 *   an independent and unofficial API. Use at your own risk.
 * - We do NOT support or tolerate anyone who wants to use this API to send spam
 *   or commit other online crimes.
 * - You will NOT use this API for marketing or other abusive purposes (spam,
 *   botting, harassment, massive bulk messaging...).
 *
 * @author mgp25: Founder, Reversing, Project Leader (https://github.com/mgp25)
 * @author SteveJobzniak (https://github.com/SteveJobzniak)
 */
class Instagram
{
    /**
     * Experiments refresh interval in sec.
     *
     * @var int
     */
    const EXPERIMENTS_REFRESH = 7200;

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
     * Google Play Advertising ID.
     *
     * The advertising ID is a unique ID for advertising, provided by Google
     * Play services for use in Google Play apps. Used by Instagram.
     *
     * @var string
     *
     * @see https://support.google.com/googleplay/android-developer/answer/6048248?hl=en
     */
    public $advertising_id;

    /**
     * Device ID.
     *
     * @var string
     */
    public $device_id;

    /**
     * Numerical UserPK ID of the active user account.
     *
     * @var string
     */
    public $account_id;

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
     * The account settings storage.
     *
     * @var \InstagramAPI\Settings\StorageHandler|null
     */
    public $settings;

    /**
     * A list of experiments enabled on per-account basis.
     *
     * @var array
     */
    public $experiments;

    /** @var Request\Live Collection of Live related functions. */
    public $live;

    /** @var Request\Direct Collection of Direct related functions. */
    public $direct;

    /** @var Request\Media Collection of Media related functions. */
    public $media;

    /** @var Request\Story Collection of Story related functions. */
    public $story;

    /** @var Request\Timeline Collection of Timeline related functions. */
    public $timeline;

    /**
     * Constructor.
     *
     * @param bool  $debug          Show API queries and responses.
     * @param bool  $truncatedDebug Truncate long responses in debug.
     * @param array $storageConfig  Configuration for the desired
     *                              user settings storage backend.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     */
    public function __construct(
        $debug = false,
        $truncatedDebug = false,
        $storageConfig = [])
    {
        // Debugging options.
        $this->debug = $debug;
        $this->truncatedDebug = $truncatedDebug;

        // Load all function collections.
        $this->live = new Request\Live($this);
        $this->direct = new Request\Direct($this);
        $this->media = new Request\Media($this);
        $this->story = new Request\Story($this);
        $this->timeline = new Request\Timeline($this);

        // Configure the settings storage and network client.
        $self = $this;
        $this->settings = Settings\Factory::createHandler(
            $storageConfig,
            [
                // This saves all user session cookies "in bulk" at script exit
                // or when switching to a different user, so that it only needs
                // to write cookies to storage a few times per user session:
                'onCloseUser' => function ($storage) use ($self) {
                    if ($self->client instanceof Client) {
                        $self->client->saveCookieJar();
                    }
                },
            ]
        );
        $this->client = new Client($this);
        $this->experiments = [];
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

        // Load all settings from the storage and mark as current user.
        $this->settings->setActiveUser($username);

        // Generate the user's Device instance, which will be created from the
        // user's last-used device IF they've got a valid, good one stored.
        // But if they've got a BAD/none, this will create a brand-new device.
        $savedDeviceString = $this->settings->get('devicestring');
        $this->device = new Devices\Device(Constants::IG_VERSION, Constants::USER_AGENT_LOCALE, $savedDeviceString);

        // Save the chosen device string to settings if not already stored.
        $deviceString = $this->device->getDeviceString();
        if ($deviceString !== $savedDeviceString) {
            $this->settings->set('devicestring', $deviceString);
        }

        // Generate a brand-new device fingerprint if the Device wasn't reused
        // from settings, OR if any of the stored fingerprints are missing.
        // NOTE: The regeneration when our device model changes is to avoid
        // dangerously reusing the "previous phone's" unique hardware IDs.
        // WARNING TO CONTRIBUTORS: Only add new parameter-checks here if they
        // are CRITICALLY important to the particular device. We don't want to
        // frivolously force the users to generate new device IDs constantly.
        $resetCookieJar = false;
        if ($deviceString !== $savedDeviceString // Brand new device, or missing
            || empty($this->settings->get('uuid')) // one of the critically...
            || empty($this->settings->get('phone_id')) // ...important device...
            || empty($this->settings->get('device_id'))) { // ...parameters.
            // Generate new hardware fingerprints.
            $this->settings->set('device_id', Signatures::generateDeviceId());
            $this->settings->set('phone_id', Signatures::generateUUID(true));
            $this->settings->set('uuid', Signatures::generateUUID(true));

            // Clear other params we also need to regenerate for the new device.
            $this->settings->set('advertising_id', '');
            $this->settings->set('experiments', '');

            // Remove the previous hardware's login details to force a relogin.
            $this->settings->set('account_id', '');
            $this->settings->set('last_login', '0');

            // We'll also need to throw out all previous cookies.
            $resetCookieJar = true;
        }

        // Generate other missing values. These are for less critical parameters
        // that don't need to trigger a complete device reset like above. For
        // example, this is good for new parameters that Instagram introduces
        // over time, since those can be added one-by-one over time without
        // needing to wipe/reset the whole device. Just be sure to also add them
        // to the "clear other params" section above so that these are always
        // properly regenerated whenever the user's whole "device" changes.
        if (empty($this->settings->get('advertising_id'))) {
            $this->settings->set('advertising_id', Signatures::generateUUID(true));
        }

        // Store various important parameters for easy access.
        $this->username = $username;
        $this->password = $password;
        $this->uuid = $this->settings->get('uuid');
        $this->advertising_id = $this->settings->get('advertising_id');
        $this->device_id = $this->settings->get('device_id');
        $this->experiments = $this->settings->getExperiments();

        // Load the previous session details if we're possibly logged in.
        if (!$resetCookieJar && $this->settings->isMaybeLoggedIn()) {
            $this->isLoggedIn = true;
            $this->account_id = $this->settings->get('account_id');
            $this->rank_token = $this->account_id.'_'.$this->uuid;
        } else {
            $this->isLoggedIn = false;
            $this->account_id = null;
            $this->rank_token = null;
        }

        // Configures Client for current user AND updates isLoggedIn state
        // if it fails to load the expected cookies from the user's jar.
        // Must be done last here, so that isLoggedIn is properly updated!
        // NOTE: If we generated a new device we start a new cookie jar.
        $this->client->updateFromCurrentSettings($resetCookieJar);
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
     * Get signup challenge headers.
     *
     * Signup challenge is used to get _csrftoken in order to make a successful
     * login or registration request.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ChallengeResponse
     */
    protected function _getSignupChallenge()
    {
        return $this->request('si/fetch_headers/')
        ->setNeedsAuth(false)
        ->addParams('challenge_type', 'signup')
        ->addParams('guid', str_replace('-', '', $this->uuid))
        ->getResponse(new Response\ChallengeResponse());
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
     *                                 This CANNOT be longer than 6 hours. Read _sendLoginFlow()!
     *                                 The shorter your delay is the BETTER. You may even want to
     *                                 set it to an even LOWER value than the default 30 minutes!
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a full (re-)login happens,
     *                                                   otherwise NULL if an existing session is resumed.
     */
    public function login(
        $forceLogin = false,
        $appRefreshInterval = 1800)
    {
        // Perform a full relogin if necessary.
        if (!$this->isLoggedIn || $forceLogin) {
            $this->syncFeatures(true);

            // Call login challenge API so a csrftoken is put in our cookie jar.
            $this->_getSignupChallenge();

            try {
                $response = $this->request('accounts/login/')
                ->setNeedsAuth(false)
                ->addPost('phone_id', $this->settings->get('phone_id'))
                ->addPost('_csrftoken', $this->client->getToken())
                ->addPost('username', $this->username)
                ->addPost('guid', $this->uuid)
                ->addPost('adid', $this->advertising_id)
                ->addPost('device_id', $this->device_id)
                ->addPost('password', $this->password)
                ->addPost('login_attempt_count', 0)
                ->getResponse(new Response\LoginResponse());
            } catch (\InstagramAPI\Exception\InstagramException $e) {
                if ($e->hasResponse() && $e->getResponse()->getTwoFactorRequired()) {
                    // Login failed because two-factor login is required.
                    // Return server response to tell user they need 2-factor.
                    return $e->getResponse();
                } else {
                    // Login failed for some other reason... Re-throw error.
                    throw $e;
                }
            }

            $this->_updateLoginState($response);

            $this->_sendLoginFlow(true, $appRefreshInterval);

            // Full (re-)login successfully completed. Return server response.
            return $response;
        }

        // Attempt to resume an existing session, or full re-login if necessary.
        // NOTE: The "return" here gives a LoginResponse in case of re-login.
        return $this->_sendLoginFlow(false, $appRefreshInterval);
    }

    /**
     * Login to Instagram using two factor authentication.
     *
     * @param string $verificationCode    Verification code you have received via SMS.
     * @param string $twoFactorIdentifier Two factor identifier, obtained in login() response. Format: 123456.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse
     */
    public function twoFactorLogin(
        $verificationCode,
        $twoFactorIdentifier)
    {
        $verificationCode = trim(str_replace(' ', '', $verificationCode));

        $response = $this->request('accounts/two_factor_login/')
        ->setNeedsAuth(false)
        ->addPost('verification_code', $verificationCode)
        ->addPost('two_factor_identifier', $twoFactorIdentifier)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('username', $this->username)
        ->addPost('device_id', $this->device_id)
        ->addPost('password', $this->password)
        ->getResponse(new Response\LoginResponse());

        $this->_updateLoginState($response);

        $this->_sendLoginFlow(true);

        return $response;
    }

    /**
     * Updates the internal state after a successful login.
     *
     * @param Response\LoginResponse $response The login response.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     */
    protected function _updateLoginState(
        Response\LoginResponse $response)
    {
        // This check is just protection against accidental bugs. It makes sure
        // that we always call this function with a *successful* login response!
        if (!$response instanceof Response\LoginResponse
            || !$response->isOk()) {
            throw new \InvalidArgumentException('Invalid login response provided to _updateLoginState().');
        }

        $this->isLoggedIn = true;
        $this->account_id = $response->getLoggedInUser()->getPk();
        $this->settings->set('account_id', $this->account_id);
        $this->rank_token = $this->account_id.'_'.$this->uuid;
        $this->settings->set('last_login', time());
    }

    /**
     * Sends login flow. This is required to emulate real device behavior.
     *
     * @param bool $justLoggedIn
     * @param int  $appRefreshInterval
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LoginResponse|null A login response if a full (re-)login is needed
     *                                                   during the login flow attempt, otherwise NULL.
     */
    protected function _sendLoginFlow(
        $justLoggedIn,
        $appRefreshInterval = 1800)
    {
        if ($appRefreshInterval > 21600) {
            throw new \InvalidArgumentException("Instagram's app state refresh interval is NOT allowed to be higher than 6 hours, and the lower the better!");
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
        if ($justLoggedIn) {
            // Perform the "user has just done a full login" API flow.
            $this->syncFeatures();
            $this->getAutoCompleteUserList();
            $this->story->getReelsTrayFeed();
            $this->direct->getRecentRecipients();
            $this->timeline->getTimelineFeed();
            $this->direct->getRankedRecipients('reshare', true);
            $this->direct->getRankedRecipients('raven', true);
            //push register
            $this->direct->getInbox();
            $this->getRecentActivity();
            $this->direct->getVisualInbox();
            //$this->getMegaphoneLog();
            $this->getExplore();
            //$this->getFacebookOTA();
        } else {
            // Act like a real logged in app client refreshing its news timeline.
            // This also lets us detect if we're still logged in with a valid session.
            try {
                $this->timeline->getTimelineFeed();
            } catch (\InstagramAPI\Exception\LoginRequiredException $e) {
                // If our session cookies are expired, we were now told to login,
                // so handle that by running a forced relogin in that case!
                return $this->login(true, $appRefreshInterval);
            }

            // Perform the "user has returned to their already-logged in app,
            // so refresh all feeds to check for news" API flow.
            $lastLoginTime = $this->settings->get('last_login');
            if (is_null($lastLoginTime) || (time() - $lastLoginTime) > $appRefreshInterval) {
                $this->settings->set('last_login', time());

                $this->getAutoCompleteUserList();
                $this->story->getReelsTrayFeed();
                $this->direct->getRankedRecipients('reshare', true);
                $this->direct->getRankedRecipients('raven', true);
                //push register
                $this->direct->getRecentRecipients();
                //push register
                $this->getMegaphoneLog();
                $this->direct->getInbox();
                $this->getRecentActivity();
                $this->getExplore();
            }

            // Users normally resume their sessions, meaning that their
            // experiments never get synced and updated. So sync periodically.
            $lastExperimentsTime = $this->settings->get('last_experiments');
            if (is_null($lastExperimentsTime) || (time() - $lastExperimentsTime) > self::EXPERIMENTS_REFRESH) {
                $this->syncFeatures();
            }
        }

        // We've now performed a login or resumed a session. Forcibly write our
        // cookies to the storage, to ensure that the storage doesn't miss them
        // in case something bad happens to PHP after this moment.
        $this->client->saveCookieJar();
    }

    /**
     * Log out of Instagram.
     *
     * WARNING: Most people should NEVER call logout()! Our library emulates
     * the Instagram app for Android, where you are supposed to stay logged in
     * forever. By calling this function, you will tell Instagram that you are
     * logging out of the APP. But you shouldn't do that! In almost 100% of all
     * cases you want to *stay logged in* so that LOGIN() resumes your session!
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LogoutResponse
     *
     * @see login()
     */
    public function logout()
    {
        $response = $this->request('accounts/logout/')
        ->getResponse(new Response\LogoutResponse());

        // We've now logged out. Forcibly write our cookies to the storage, to
        // ensure that the storage doesn't miss them in case something bad
        // happens to PHP after this moment.
        $this->client->saveCookieJar();

        return $response;
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
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('old_password', $oldPassword)
            ->addPost('new_password1', $newPassword)
            ->addPost('new_password2', $newPassword)
            ->getResponse(new Response\ChangePasswordResponse());
    }

    /**
     * Request that Instagram enables two factor SMS authentication.
     *
     * The SMS will have a verification code for enabling two factor SMS
     * authentication. You must then give that code to enableTwoFactor().
     *
     * @param string $phoneNumber Phone number with country code. Format: +34123456789.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RequestTwoFactorResponse
     *
     * @see enableTwoFactor()
     */
    public function requestTwoFactor(
        $phoneNumber)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        return $this->request('accounts/send_two_factor_enable_sms/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('device_id', $this->device_id)
        ->addPost('phone_number', $cleanNumber)
        ->getResponse(new Response\RequestTwoFactorResponse());
    }

    /**
     * Enable Two Factor authentication.
     *
     * @param string $phoneNumber      Phone number with country code. Format: +34123456789.
     * @param string $verificationCode The code sent to your phone via requestTwoFactor().
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see requestTwoFactor()
     * @see getAccountSecurityInfo()
     */
    public function enableTwoFactor(
        $phoneNumber,
        $verificationCode)
    {
        $cleanNumber = '+'.preg_replace('/[^0-9]/', '', $phoneNumber);

        $response = $this->request('accounts/enable_sms_two_factor/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('device_id', $this->device_id)
        ->addPost('phone_number', $cleanNumber)
        ->addPost('verification_code', $verificationCode)
        ->getResponse(new Response\EnableTwoFactorResponse());

        return $this->getAccountSecurityInfo();
    }

    /**
     * Disable Two Factor authentication.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DisableTwoFactorResponse
     */
    public function disableTwoFactor()
    {
        return $this->request('accounts/disable_sms_two_factor/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->getResponse(new Response\DisableTwoFactorResponse());
    }

    /**
     * Get account security info and backup codes.
     *
     * WARNING: STORE AND KEEP BACKUP CODES IN A SAFE PLACE. THEY ARE EXTREMELY
     *          IMPORTANT! YOU WILL GET THE CODES IN THE RESPONSE. THE BACKUP
     *          CODES LET YOU REGAIN CONTROL OF YOUR ACCOUNT IF YOU LOSE THE
     *          PHONE NUMBER! WITHOUT THE CODES, YOU RISK LOSING YOUR ACCOUNT!
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\AccountSecurityInfoResponse
     *
     * @see enableTwoFactor()
     */
    public function getAccountSecurityInfo()
    {
        return $this->request('accounts/account_security_info/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->getResponse(new Response\AccountSecurityInfoResponse());
    }

    /**
     * Save experiments.
     *
     * @param Response\SyncResponse $syncResponse
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    protected function _saveExperiments(
        Response\SyncResponse $syncResponse)
    {
        $experiments = [];
        foreach ($syncResponse->experiments as $experiment) {
            if (!isset($experiment->name)) {
                continue;
            }

            $group = $experiment->name;
            if (!isset($experiments[$group])) {
                $experiments[$group] = [];
            }

            if (!isset($experiment->params)) {
                continue;
            }

            foreach ($experiment->params as $param) {
                if (!isset($param->name)) {
                    continue;
                }

                $experiments[$group][$param->name] = $param->value;
            }
        }
        $this->experiments = $this->settings->setExperiments($experiments);
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
            ->setNeedsAuth(false)
            ->addPost('id', Signatures::generateUUID(true))
            ->addPost('experiments', Constants::LOGIN_EXPERIMENTS)
            ->getResponse(new Response\SyncResponse());
        } else {
            $result = $this->request('qe/sync/')
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('id', $this->account_id)
            ->addPost('experiments', Constants::EXPERIMENTS)
            ->getResponse(new Response\SyncResponse());

            // Save the experiments and the last time we refreshed them.
            $this->_saveExperiments($result);
            $this->settings->set('last_experiments', time());

            return $result;
        }
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
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
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
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
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
     * Set your account's first name and phone.
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
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('first_name', $name)
            ->addPost('phone_number', $phone)
            ->getResponse(new \InstagramAPI\Response());
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
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
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
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
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
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\UserInfoResponse());
    }

    /**
     * Get account spam filter status.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterResponse
     */
    public function getCommentFilter()
    {
        return $this->request('accounts/get_comment_filter/')
            ->getResponse(new Response\CommentFilterResponse());
    }

    /**
     * Set account spam filter status (on/off).
     *
     * @param int $config_value Whether spam filter is on (0 or 1).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterSetResponse
     */
    public function setCommentFilter(
        $config_value)
    {
        return $this->request('accounts/set_comment_filter/')
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('config_value', $config_value)
            ->setSignedPost(true)
            ->getResponse(new Response\CommentFilterSetResponse());
    }

    /**
     * Get account spam filter keywords.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterKeywordsResponse
     */
    public function getCommentFilterKeywords()
    {
        return $this->request('accounts/get_comment_filter_keywords/')
            ->getResponse(new Response\CommentFilterKeywordsResponse());
    }

    /**
     * Set account spam filter keywords.
     *
     * @param string $keywords List of blocked words, separated by comma.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\CommentFilterSetResponse
     */
    public function setCommentFilterKeywords(
        $keywords)
    {
        return $this->request('accounts/set_comment_filter_keywords/')
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('keywords', $keywords)
            ->setSignedPost(true)
            ->getResponse(new Response\CommentFilterSetResponse());
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
            ->addParams('version', '2');

            return $request->getResponse(new Response\AutoCompleteUserListResponse());
        } catch (\InstagramAPI\Exception\ThrottledException $e) {
            // Throttling is so common that we'll simply return NULL in that case.
            return;
        } catch (\InstagramAPI\Exception\InstagramException $e) {
            // If any other errors happen, we'll still return the server reply.
            return $e->getResponse();
        }
    }

    /**
     * Register to the mqtt push server.
     *
     * @param $gcmToken
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PushRegisterResponse
     */
    public function pushRegister(
        $gcmToken)
    {
        $deviceToken = json_encode([
            'k' => $gcmToken,
            'v' => 0,
            't' => 'fbns-b64',
        ]);

        return $this->request('push/register/')
        ->addParams('platform', '10')
        ->addParams('device_type', 'android_mqtt')
        ->addPost('_uuid', $this->uuid)
        ->addPost('guid', $this->uuid)
        ->addPost('phone_id', $this->settings->get('phone_id'))
        ->addPost('device_type', 'android_mqtt')
        ->addPost('device_token', $deviceToken)
        ->addPost('is_main_push_channel', true)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('users', $this->account_id)
        ->getResponse(new Response\PushRegisterResponse());
    }

    /**
     * Get push preferences.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PushPreferencesResponse
     */
    public function getPushPreferences()
    {
        return $this->request('push/all_preferences/')
        ->getResponse(new Response\PushPreferencesResponse());
    }

    /**
     * Get Facebook OTA.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookOTAResponse
     */
    public function getFacebookOTA()
    {
        return $this->request('facebook_ota/')
        ->addParams('fields', Constants::FACEBOOK_OTA_FIELDS)
        ->addParams('custom_user_id', $this->account_id)
        ->addParams('signed_body', Signatures::generateSignature('').'.')
        ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION)
        ->addParams('version_code', Constants::VERSION_CODE)
        ->addParams('version_name', Constants::IG_VERSION)
        ->addParams('custom_app_id', Constants::FACEBOOK_ORCA_APPLICATION_ID)
        ->addParams('custom_device_id', $this->uuid)
        ->getResponse(new Response\FacebookOTAResponse());
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
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('uuid', md5(time()))
        ->getResponse(new Response\MegaphoneLogResponse());
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
        ->addPost('_uid', $this->account_id)
        ->addPost('id', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('experiment', 'ig_android_profile_contextual_feed')
        ->getResponse(new Response\ExposeResponse());
    }

    /**
     * INTERNAL. UPLOADS A *SINGLE* PHOTO.
     *
     * @param string $targetFeed       Target feed for this media ("timeline", "story",
     *                                 but NOT "album", they are handled elsewhere).
     * @param string $photoFilename    The photo filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see configureSinglePhoto() for available metadata fields.
     */
    public function uploadSinglePhoto(
        $targetFeed,
        $photoFilename,
        array $externalMetadata = [])
    {
        // Make sure we only allow these particular feeds for this function.
        if ($targetFeed != 'timeline' && $targetFeed != 'story') {
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Verify that the file exists locally.
        if (!is_file($photoFilename)) {
            throw new \InvalidArgumentException(sprintf('The photo file "%s" does not exist on disk.', $photoFilename));
        }

        // Determine the width and height of the photo.
        $imagesize = @getimagesize($photoFilename);
        if ($imagesize === false) {
            throw new \InvalidArgumentException(sprintf('File "%s" is not an image.', $photoFilename));
        }
        list($photoWidth, $photoHeight) = $imagesize;

        // Validate image resolution and aspect ratio.
        Utils::throwIfIllegalMediaResolution($targetFeed, 'photofile', $photoFilename, $photoWidth, $photoHeight);

        // Perform the upload.
        $upload = $this->client->uploadPhotoData($targetFeed, $photoFilename);

        // Configure the uploaded image and attach it to our timeline/story.
        $internalMetadata = [
            'uploadId'      => $upload->getUploadId(),
            'photoWidth'    => $photoWidth,
            'photoHeight'   => $photoHeight,
        ];
        $configure = $this->configureSinglePhoto($targetFeed, $internalMetadata, $externalMetadata);

        return $configure;
    }

    /**
     * INTERNAL. UPLOADS A *SINGLE* VIDEO.
     *
     * @param string $targetFeed       Target feed for this media ("timeline", "story",
     *                                 but NOT "album", they are handled elsewhere).
     * @param string $videoFilename    The video filename.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     * @param int    $maxAttempts      (optional) Total attempts to upload all chunks before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     * @throws \InstagramAPI\Exception\UploadFailedException If the video-data upload fails.
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see configureSingleVideo() for available metadata fields.
     */
    public function uploadSingleVideo(
        $targetFeed,
        $videoFilename,
        array $externalMetadata = [],
        $maxAttempts = 10)
    {
        // Make sure we only allow these particular feeds for this function.
        if ($targetFeed != 'timeline' && $targetFeed != 'story') {
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Verify that the file exists locally.
        if (!is_file($videoFilename)) {
            throw new \InvalidArgumentException(sprintf('The video file "%s" does not exist on disk.', $videoFilename));
        }

        $internalMetadata = [];

        // Figure out the video file details.
        // NOTE: We do this first, since it validates whether the video file is
        // valid and lets us avoid wasting time uploading totally invalid files!
        $internalMetadata['videoDetails'] = Utils::getVideoFileDetails($videoFilename);

        // Validate the video details and throw if Instagram won't allow it.
        Utils::throwIfIllegalVideoDetails($targetFeed, $videoFilename, $internalMetadata['videoDetails']);

        // Request parameters for uploading a new video.
        $uploadParams = $this->client->requestVideoUploadURL($targetFeed, $internalMetadata);
        $internalMetadata['uploadId'] = $uploadParams['uploadId'];

        // Attempt to upload the video data.
        $upload = $this->client->uploadVideoChunks($targetFeed, $videoFilename, $uploadParams, $maxAttempts);

        // Attempt to upload the thumbnail, associated with our video's ID.
        $this->client->uploadPhotoData($targetFeed, $videoFilename, 'videofile', $uploadParams['uploadId']);

        // Configure the uploaded video and attach it to our timeline/story.
        $configure = $this->configureSingleVideoWithRetries($targetFeed, $internalMetadata, $externalMetadata);

        return $configure;
    }

    /**
     * Configures parameters for a *SINGLE* uploaded photo file.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE* AND *STORY* -PHOTOS-.
     * USE "configureTimelineAlbum()" FOR ALBUMS and "configureSingleVideo()" FOR VIDEOS.
     * AND IF FUTURE INSTAGRAM FEATURES NEED CONFIGURATION AND ARE NON-TRIVIAL,
     * GIVE THEM THEIR OWN FUNCTION LIKE WE DID WITH "configureTimelineAlbum()",
     * TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB CODE!
     *
     * @param string $targetFeed       Target feed for this media ("timeline", "story",
     *                                 but NOT "album", they are handled elsewhere).
     * @param array  $internalMetadata Internal library-generated metadata key-value pairs.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureSinglePhoto(
        $targetFeed,
        array $internalMetadata,
        array $externalMetadata = [])
    {
        // Determine the target endpoint for the photo.
        switch ($targetFeed) {
        case 'timeline':
            $endpoint = 'media/configure/';
            break;
        case 'story':
            $endpoint = 'media/configure_to_story/';
            break;
        default:
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Available external metadata parameters:
        /** @var string|null Caption to use for the media. NOT USED FOR STORY MEDIA! */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : null;
        /** @var Response\Model\Location|null A Location object describing where
         the media was taken. NOT USED FOR STORY MEDIA! */
        $location = (isset($externalMetadata['location']) && $targetFeed != 'story') ? $externalMetadata['location'] : null;
        /** @var void Photo filter. THIS DOES NOTHING! All real filters are done in the mobile app. */
        // $filter = isset($externalMetadata['filter']) ? $externalMetadata['filter'] : null;
        $filter = null; // COMMENTED OUT SO USERS UNDERSTAND THEY CAN'T USE THIS!

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        // Critically important internal library-generated metadata parameters:
        /** @var string The ID of the entry to configure. */
        $uploadId = $internalMetadata['uploadId'];
        /** @var int|float Width of the photo. */
        $photoWidth = $internalMetadata['photoWidth'];
        /** @var int|float Height of the photo. */
        $photoHeight = $internalMetadata['photoHeight'];

        // Build the request...
        $requestData = $this->request($endpoint)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('_uid', $this->account_id)
        ->addPost('_uuid', $this->uuid)
        ->addPost('edits',
            [
                'crop_original_size'    => [$photoWidth, $photoHeight],
                'crop_zoom'             => 1,
                'crop_center'           => [0.0, -0.0],
            ])
        ->addPost('device',
            [
                'manufacturer'      => $this->device->getManufacturer(),
                'model'             => $this->device->getModel(),
                'android_version'   => $this->device->getAndroidVersion(),
                'android_release'   => $this->device->getAndroidRelease(),
            ])
        ->addPost('extra',
            [
                'source_width'  => $photoWidth,
                'source_height' => $photoHeight,
            ]);

        switch ($targetFeed) {
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
            ->addPost('media_longitude', $location->getLng())
            ->addPost('av_latitude', 0.0)
            ->addPost('av_longitude', 0.0);
        }

        $configure = $requestData->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Helper function for reliably configuring videos.
     *
     * Exactly the same as configureSingleVideo() but performs multiple attempts. Very
     * useful since Instagram sometimes can't configure a newly uploaded video
     * file until a few seconds have passed.
     *
     * @param string $targetFeed       Target feed for this media ("timeline", "story",
     *                                 but NOT "album", they are handled elsewhere).
     * @param array  $internalMetadata Internal library-generated metadata key-value pairs.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     * @param int    $maxAttempts      Total attempts to configure video before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see configureSingleVideo() for available metadata fields.
     */
    public function configureSingleVideoWithRetries(
        $targetFeed,
        array $internalMetadata,
        array $externalMetadata = [],
        $maxAttempts = 5)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            try {
                // Attempt to configure video parameters.
                $configure = $this->configureSingleVideo($targetFeed, $internalMetadata, $externalMetadata);
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

        return $configure; // ConfigureResponse
    }

    /**
     * Configures parameters for a *SINGLE* uploaded video file.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE* AND *STORY* -VIDEOS-.
     * USE "configureTimelineAlbum()" FOR ALBUMS and "configureSinglePhoto()" FOR PHOTOS.
     * AND IF FUTURE INSTAGRAM FEATURES NEED CONFIGURATION AND ARE NON-TRIVIAL,
     * GIVE THEM THEIR OWN FUNCTION LIKE WE DID WITH "configureTimelineAlbum()",
     * TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB CODE!
     *
     * @param string $targetFeed       Target feed for this media ("timeline", "story",
     *                                 but NOT "album", they are handled elsewhere).
     * @param array  $internalMetadata Internal library-generated metadata key-value pairs.
     * @param array  $externalMetadata (optional) User-provided metadata key-value pairs.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureSingleVideo(
        $targetFeed,
        array $internalMetadata,
        array $externalMetadata = [])
    {
        // Determine the target endpoint for the video.
        switch ($targetFeed) {
        case 'timeline':
            $endpoint = 'media/configure/';
            break;
        case 'story':
            $endpoint = 'media/configure_to_story/';
            break;
        default:
            throw new \InvalidArgumentException(sprintf('Bad target feed "%s".', $targetFeed));
        }

        // Available external metadata parameters:
        /** @var string|null Caption to use for the media. */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : null;
        /** @var string[]|null Array of numerical UserPK IDs of people tagged in
         * your video. ONLY USED IN STORY VIDEOS! TODO: Actually, it's not even
         * implemented for stories. */
        $userTags = (isset($externalMetadata['usertags']) && $targetFeed == 'story') ? $externalMetadata['usertags'] : null;
        /** @var Response\Model\Location|null A Location object describing where
         the media was taken. NOT USED FOR STORY MEDIA! */
        $location = (isset($externalMetadata['location']) && $targetFeed != 'story') ? $externalMetadata['location'] : null;

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        // Critically important internal library-generated metadata parameters:
        /** @var string The ID of the entry to configure. */
        $uploadId = $internalMetadata['uploadId'];
        /** @var array Video details array. */
        $videoDetails = $internalMetadata['videoDetails'];

        // Build the request...
        $requestData = $this->request($endpoint)
        ->addParams('video', 1)
        ->addPost('video_result', 'deprecated')
        ->addPost('upload_id', $uploadId)
        ->addPost('poster_frame_index', 0)
        ->addPost('length', round($videoDetails['duration'], 1))
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
                'source_width'  => $videoDetails['width'],
                'source_height' => $videoDetails['height'],
            ])
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id);

        if ($targetFeed == 'story') {
            $requestData->addPost('configure_mode', 1) // 1 - REEL_SHARE, 2 - DIRECT_STORY_SHARE
            ->addPost('story_media_creation_date', time() - mt_rand(10, 20))
            ->addPost('client_shared_at', time() - mt_rand(3, 10))
            ->addPost('client_timestamp', time());
        }

        $requestData->addPost('caption', $captionText);

        if ($targetFeed == 'story') {
            $requestData->addPost('story_media_creation_date', time());
            if (!is_null($userTags)) {
                // Reel Mention example:
                // [{\"y\":0.3407772676161919,\"rotation\":0,\"user_id\":\"USER_ID\",\"x\":0.39892578125,\"width\":0.5619921875,\"height\":0.06011525487256372}]
                // NOTE: The backslashes are just double JSON encoding, ignore
                // that and just give us an array with these clean values, don't
                // try to encode it in any way, we do all encoding to match the above.
                // This post field will get wrapped in another json_encode call during transfer.
                $requestData->addPost('reel_mentions', json_encode($userTags));
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
            ->addPost('posting_latitude', $location->getLat())
            ->addPost('posting_longitude', $location->getLng())
            ->addPost('media_latitude', $location->getLat())
            ->addPost('media_longitude', $location->getLng())
            ->addPost('av_latitude', 0.0)
            ->addPost('av_longitude', 0.0);
        }

        $configure = $requestData->getResponse(new Response\ConfigureResponse());

        return $configure;
    }

    /**
     * Helper function for reliably configuring albums.
     *
     * Exactly the same as configureTimelineAlbum() but performs multiple
     * attempts. Very useful since Instagram sometimes can't configure a newly
     * uploaded video file until a few seconds have passed.
     *
     * @param array $media            Extended media array coming from timeline->uploadAlbum(),
     *                                containing the user's per-file metadata,
     *                                and internally generated per-file metadata.
     * @param array $internalMetadata Internal library-generated metadata key-value pairs.
     * @param array $externalMetadata (optional) User-provided metadata key-value pairs
     *                                for the album itself (its caption, location, etc).
     * @param int   $maxAttempts      Total attempts to configure videos before throwing.
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     *
     * @see configureTimelineAlbum() for available metadata fields.
     */
    public function configureTimelineAlbumWithRetries(
        array $media,
        array $internalMetadata,
        array $externalMetadata = [],
        $maxAttempts = 5)
    {
        for ($attempt = 1; $attempt <= $maxAttempts; ++$attempt) {
            try {
                // Attempt to configure album parameters.
                $configure = $this->configureTimelineAlbum($media, $internalMetadata, $externalMetadata);
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

        return $configure; // ConfigureResponse
    }

    /**
     * Configures parameters for a whole album of uploaded media files.
     *
     * WARNING TO CONTRIBUTORS: THIS IS ONLY FOR *TIMELINE ALBUMS*. DO NOT MAKE
     * IT DO ANYTHING ELSE, TO AVOID ADDING BUGGY AND UNMAINTAINABLE SPIDERWEB
     * CODE!
     *
     * @param array $media            Extended media array coming from timeline->uploadAlbum(),
     *                                containing the user's per-file metadata,
     *                                and internally generated per-file metadata.
     * @param array $internalMetadata Internal library-generated metadata key-value pairs.
     * @param array $externalMetadata (optional) User-provided metadata key-value pairs
     *                                for the album itself (its caption, location, etc).
     *
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ConfigureResponse
     */
    public function configureTimelineAlbum(
        array $media,
        array $internalMetadata,
        array $externalMetadata = [])
    {
        $endpoint = 'media/configure_sidecar/';

        // Available external metadata parameters:
        /** @var string|null Caption to use for the album. */
        $captionText = isset($externalMetadata['caption']) ? $externalMetadata['caption'] : null;
        /** @var Response\Model\Location|null A Location object describing where
         the album was taken. */
        $location = isset($externalMetadata['location']) ? $externalMetadata['location'] : null;

        // Fix very bad external user-metadata values.
        if (!is_string($captionText)) {
            $captionText = '';
        }

        // Critically important internal library-generated metadata parameters:
        // NOTE: NO INTERNAL DATA IS NEEDED HERE YET.

        // Build the album's per-children metadata.
        $date = date('Y:m:d H:i:s');
        $childrenMetadata = [];
        foreach ($media as $item) {
            // Get all of the common, INTERNAL per-file metadata.
            $uploadId = $item['internalMetadata']['uploadId'];

            switch ($item['type']) {
            case 'photo':
                // Get all of the INTERNAL per-PHOTO metadata.
                /** @var int|float */
                $photoWidth = $item['internalMetadata']['photoWidth'];
                /** @var int|float */
                $photoHeight = $item['internalMetadata']['photoHeight'];

                // Build this item's configuration.
                $photoConfig = [
                    'date_time_original'  => $date,
                    'scene_type'          => 1,
                    'disable_comments'    => false,
                    'upload_id'           => $uploadId,
                    'source_type'         => 0,
                    'scene_capture_type'  => 'standard',
                    'date_time_digitized' => $date,
                    'geotag_enabled'      => false,
                    'camera_position'     => 'back',
                    'edits'               => [
                        'filter_strength' => 1,
                        'filter_name'     => 'IGNormalFilter',
                    ],
                ];

                // This usertag per-file EXTERNAL metadata is only supported for PHOTOS!
                if (isset($item['usertags'])) {
                    $photoConfig['usertags'] = json_encode(['in' => $item['usertags']]);
                }

                $childrenMetadata[] = $photoConfig;
                break;
            case 'video':
                // Get all of the INTERNAL per-VIDEO metadata.
                /** @var array Video details array. */
                $videoDetails = $item['internalMetadata']['videoDetails'];

                // Build this item's configuration.
                $videoConfig = [
                    'length'              => round($videoDetails['duration'], 1),
                    'date_time_original'  => $date,
                    'scene_type'          => 1,
                    'poster_frame_index'  => 0,
                    'trim_type'           => 0,
                    'disable_comments'    => false,
                    'upload_id'           => $uploadId,
                    'source_type'         => 'library',
                    'geotag_enabled'      => false,
                    'edits'               => [
                        'length'          => round($videoDetails['duration'], 1),
                        'cinema'          => 'unsupported',
                        'original_length' => round($videoDetails['duration'], 1),
                        'source_type'     => 'library',
                        'start_time'      => 0,
                        'camera_position' => 'unknown',
                        'trim_type'       => 0,
                    ],
                ];

                $childrenMetadata[] = $videoConfig;
                break;
            }
        }

        // Build the request...
        $requestData = $this->request($endpoint)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('_uid', $this->account_id)
        ->addPost('_uuid', $this->uuid)
        ->addPost('client_sidecar_id', Utils::generateUploadId())
        ->addPost('caption', $captionText)
        ->addPost('children_metadata', $childrenMetadata);

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
            ->addPost('media_longitude', $location->getLng())
            ->addPost('exif_latitude', 0.0)
            ->addPost('exif_longitude', 0.0);
        }

        $configure = $requestData->getResponse(new Response\ConfigureResponse());

        return $configure;
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
        return $this->getUserTags($this->account_id);
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
     * Search for users by linking your address book to Instagram.
     *
     * @param array $contacts
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\LinkAddressBookResponse
     */
    public function linkAddressBook(
        $contacts)
    {
        return $this->request('address_book/link/?include=extra_display_name,thumbnails')
            ->setSignedPost(false)
            ->addPost('contacts', json_encode($contacts, true))
            ->getResponse(new Response\LinkAddressBookResponse());
    }

    /**
     * Unlink your address book from Instagram.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UnlinkAddressBookResponse
     */
    public function unlinkAddressBook()
    {
        return $this->request('address_book/unlink/')
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->account_id)
            ->addPost('_csrftoken', $this->client->getToken())
            ->getResponse(new Response\UnlinkAddressBookResponse());
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
     * Get details about a specific user via their numerical UserPK ID.
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
        return $this->getUserInfoById($this->account_id);
    }

    /**
     * Get the numerical UserPK ID for a specific user via their username.
     *
     * This is just a convenient helper function. You may prefer to use
     * getUserInfoByName() instead, which lets you see more details.
     *
     * @param string $username Username as string (NOT as a numerical ID).
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return string Their numerical UserPK ID.
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
     * Get location based media feed for a user.
     *
     * Note that you probably want timeline->getUserFeed() instead, because
     * the geographical feed does not contain all of the user's media.
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
        return $this->getGeoMedia($this->account_id);
    }

    /**
     * Search for nearby Instagram locations by geographical coordinates.
     *
     * @param string      $latitude
     * @param string      $longitude
     * @param null|string $query
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
     * Search for related locations by location ID.
     *
     * @param string $locationId
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\RelatedLocationResponse
     */
    public function searchRelatedLocation(
        $locationId)
    {
        return $this->request("locations/{$locationId}/related")
        ->addParams('visited', json_encode(['id' => $locationId, 'type' => 'location']))
        ->addParams('related_types', json_encode(['location']))
        ->getResponse(new Response\RelatedLocationResponse());
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
        return $this->getUserFollowings($this->account_id, $maxId);
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
        return $this->getUserFollowers($this->account_id, $maxId);
    }

    /**
     * Get list of pending friendship requests.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FollowerAndFollowingResponse
     */
    public function getPendingFriendshipRequests()
    {
        $requestData = $this->request('friendships/pending/');

        return $requestData->getResponse(new Response\FollowerAndFollowingResponse());
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
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('user_id', $userId)
        ->addPost('radio_type', 'wifi-none')
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
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('user_id', $userId)
        ->addPost('radio_type', 'wifi-none')
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Report media in the Explore-feed.
     *
     * @param string $exploreSourceToken Token related to the Explore media.
     * @param string $userId             Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ReportExploreMediaResponse
     */
    public function reportExploreMedia(
        $exploreSourceToken,
        $userId)
    {
        return $this->request('discover/explore_report/')
        ->addParam('explore_source_token', $exploreSourceToken)
        ->addParam('m_pk', $this->account_id)
        ->addParam('a_pk', $userId)
        ->getResponse(new Response\ReportExploreMediaResponse());
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
     * Get suggested users via account badge.
     *
     * This is the endpoint for when you press the "user icon with
     * the plus sign" on your own profile in the Instagram app.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SuggestedUsersBadgeResponse
     */
    public function getSuggestedUsersBadge()
    {
        return $this->request('discover/profile_su_badge/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('module', 'discover_people')
        ->getResponse(new Response\SuggestedUsersBadgeResponse());
    }

    /**
     * Get badge notifications.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BadgeNotificationsResponse
     */
    public function getBadgeNotifications()
    {
        return $this->request('notifications/badge/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('users_ids', $this->account_id)
        ->addPost('device_id', $this->device_id)
        ->getResponse(new Response\BadgeNotificationsResponse());
    }

    /**
     * Hide suggested user.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SuggestedUsersResponse
     */
    public function hideSuggestedUser(
        $userId)
    {
        return $this->request('discover/aysf_dismiss/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addParams('target_id', $userId)
        ->addParams('algorithm', 'ig_friends_of_friends_from_tao_laser_algorithm')
        ->getResponse(new Response\SuggestedUsersResponse());
    }

    /**
     * Discover new people via Facebook's algorithm.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\DiscoverPeopleResponse
     */
    public function discoverPeople()
    {
        return $this->request('discover/ayml/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('paginate', true)
        ->addPost('module', 'discover_people')
        ->getResponse(new Response\DiscoverPeopleResponse());
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
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
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
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('user_id', $userId)
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Get a list of all blocked users.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BlockedListResponse
     */
    public function getBlockedList()
    {
        return $this->request('users/blocked_list/')
        ->getResponse(new Response\BlockedListResponse());
    }

    /**
     * Block a user's ability to see your stories.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     *
     * @see muteFriendStory()
     */
    public function blockFriendStory(
        $userId)
    {
        return $this->request("friendships/block_friend_reel/{$userId}/")
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('source', 'profile')
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Unblock a user so that they can see your stories again.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     *
     * @see unmuteFriendStory()
     */
    public function unblockFriendStory(
        $userId)
    {
        return $this->request("friendships/unblock_friend_reel/{$userId}/")
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->addPost('source', 'profile')
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Get the list of users who are blocked from seeing your stories.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BlockedReelsResponse
     */
    public function getBlockedStoryList()
    {
        return $this->request('friendships/blocked_reels/')
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->getResponse(new Response\BlockedReelsResponse());
    }

    /**
     * Mute a friend's stories, so that you no longer see their stories.
     *
     * This does not block them from seeing *your* stories.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     *
     * @see blockFriendStory()
     */
    public function muteFriendStory(
        $userId)
    {
        return $this->request("friendships/mute_friend_reel/{$userId}/")
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
        ->getResponse(new Response\FriendshipResponse());
    }

    /**
     * Unmute a friend's stories, so that you see their stories again.
     *
     * This does not unblock them from seeing *your* stories.
     *
     * @param string $userId Numerical UserPK ID.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FriendshipResponse
     *
     * @see unblockFriendStory()
     */
    public function unmuteFriendStory(
        $userId)
    {
        return $this->request("friendships/unmute_friend_reel/{$userId}/")
        ->setSignedPost(true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->account_id)
        ->addPost('_csrftoken', $this->client->getToken())
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
        ->addPost('_csrftoken', $this->client->getToken())
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
     * Tell Instagram to send you a message to verify your email address.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\SendConfirmEmailResponse
     */
    public function sendConfirmEmail()
    {
        return $this->request('accounts/send_confirm_email/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('send_source', 'profile_megaphone')
        ->addPost('_csrftoken', $this->client->getToken())
        ->getResponse(new Response\SendConfirmEmailResponse());
    }

    /**
     * Get sticker assets.
     *
     * @param string $stickerType Type of sticker (currently only "static_stickers").
     * @param array  $location    Array containing lat, lng and horizontalAccuracy.
     *
     * @throws \InstagramAPI\Exception\InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\StickerAssetsResponse
     */
    public function getStickerAssets(
        $stickerType = 'static_stickers',
        $location = null)
    {
        if ($stickerType != 'static_stickers') {
            throw new \InvalidArgumentException('You must provide a valid sticker type.');
        }
        if (!is_null($location) && (!isset($location['lat']) || !isset($location['lng']) || !isset($location['horizontalAccuracy']))) {
            throw new \InvalidArgumentException('Your location array must contain keys for "lat", "lng" and "horizontalAccuracy".');
        }

        $requestData = $this->request('creatives/assets/')
        ->setSignedPost(true)
        ->addPost('type', $stickerType);

        if (!is_null($location)) {
            $requestData->addPost('lat', $location['lat'])
            ->addPost('lng', $location['lat'])
            ->addPost('horizontalAccuracy', $location['horizontalAccuracy']);
        }

        $requestData->getResponse(new Response\StickerAssetsResponse());
    }

    /**
     * Checks if param is enabled in given experiment.
     *
     * @param string $experiment
     * @param string $param
     *
     * @return bool
     */
    public function isExperimentEnabled(
        $experiment,
        $param)
    {
        return isset($this->experiments[$experiment][$param])
            && in_array($this->experiments[$experiment][$param], ['enabled', 'true', '1']);
    }

    /**
     * Checks if current user has a unified inbox.
     *
     * @return bool
     */
    public function hasUnifiedInbox()
    {
        return $this->isExperimentEnabled('ig_android_unified_inbox', 'is_enabled');
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
