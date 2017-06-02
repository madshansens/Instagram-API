<?php

namespace InstagramAPI;

/**
 * Instagram's Private API v3.
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

    /** @var Request\Business Collection of Business related functions. */
    public $business;
    /** @var Request\Direct Collection of Direct related functions. */
    public $direct;
    /** @var Request\Hashtag Collection of Hashtag related functions. */
    public $hashtag;
    /** @var Request\Internal Collection of Internal (non-public) functions. */
    public $internal;
    /** @var Request\Live Collection of Live related functions. */
    public $live;
    /** @var Request\Location Collection of Location related functions. */
    public $location;
    /** @var Request\Media Collection of Media related functions. */
    public $media;
    /** @var Request\People Collection of People related functions. */
    public $people;
    /** @var Request\Story Collection of Story related functions. */
    public $story;
    /** @var Request\Timeline Collection of Timeline related functions. */
    public $timeline;
    /** @var Request\Usertag Collection of Usertag related functions. */
    public $usertag;

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
        $this->business = new Request\Business($this);
        $this->direct = new Request\Direct($this);
        $this->hashtag = new Request\Hashtag($this);
        $this->internal = new Request\Internal($this);
        $this->live = new Request\Live($this);
        $this->location = new Request\Location($this);
        $this->media = new Request\Media($this);
        $this->people = new Request\People($this);
        $this->story = new Request\Story($this);
        $this->timeline = new Request\Timeline($this);
        $this->usertag = new Request\Usertag($this);

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
     * @see http://php.net/curl_setopt CURLOPT_INTERFACE
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
     * Registers advertising identifier.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response
     */
    public function logAttribution()
    {
        return $this->request('attribution/log_attribution/')
            ->setNeedsAuth(false)
            ->addPost('adid', $this->advertising_id)
            ->getResponse(new Response());
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
            $this->logAttribution();

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
            $this->people->getAutoCompleteUserList();
            $this->story->getReelsTrayFeed();
            $this->direct->getRecentRecipients();
            $this->timeline->getTimelineFeed();
            $this->direct->getRankedRecipients('reshare', true);
            $this->direct->getRankedRecipients('raven', true);
            //push register
            $this->direct->getInbox();
            $this->people->getRecentActivityInbox();
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

                $this->people->getAutoCompleteUserList();
                $this->story->getReelsTrayFeed();
                $this->direct->getRankedRecipients('reshare', true);
                $this->direct->getRankedRecipients('raven', true);
                //push register
                $this->direct->getRecentRecipients();
                //push register
                $this->getMegaphoneLog();
                $this->direct->getInbox();
                $this->people->getRecentActivityInbox();
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
     * @see Instagram::login()
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
     * @see Instagram::enableTwoFactor()
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
     * @see Instagram::requestTwoFactor()
     * @see Instagram::getAccountSecurityInfo()
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
     * @see Instagram::enableTwoFactor()
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

        // Save the experiments and the last time we refreshed them.
        $this->experiments = $this->settings->setExperiments($experiments);
        $this->settings->set('last_experiments', time());
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
                ->addPost('id', $this->uuid)
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

            // Save the updated experiments for this user.
            $this->_saveExperiments($result);

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
            ->addParam('edit', true)
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
     * @throws \InvalidArgumentException
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\UserInfoResponse
     */
    public function changeProfilePicture(
        $photoFilename)
    {
        return $this->request('accounts/change_profile_picture/')
            ->addPost('_csrftoken', $this->client->getToken())
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->account_id)
            ->addFile('profile_pic', $photoFilename, 'profile_pic')
            ->getResponse(new Response\UserInfoResponse());
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
            ->getResponse(new Response\CommentFilterSetResponse());
    }

    /**
     * Get Facebook OTA (Over-The-Air) update information.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\FacebookOTAResponse
     */
    public function getFacebookOTA()
    {
        return $this->request('facebook_ota/')
            ->addParam('fields', Constants::FACEBOOK_OTA_FIELDS)
            ->addParam('custom_user_id', $this->account_id)
            ->addParam('signed_body', Signatures::generateSignature('').'.')
            ->addParam('ig_sig_key_version', Constants::SIG_KEY_VERSION)
            ->addParam('version_code', Constants::VERSION_CODE)
            ->addParam('version_name', Constants::IG_VERSION)
            ->addParam('custom_app_id', Constants::FACEBOOK_ORCA_APPLICATION_ID)
            ->addParam('custom_device_id', $this->uuid)
            ->getResponse(new Response\FacebookOTAResponse());
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
     * Get popular feed.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\PopularFeedResponse
     */
    public function getPopularFeed()
    {
        return $this->request('feed/popular/')
            ->addParam('people_teaser_supported', '1')
            ->addParam('rank_token', $this->rank_token)
            ->addParam('ranked_content', 'true')
            ->getResponse(new Response\PopularFeedResponse());
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
     * @param string     $stickerType Type of sticker (currently only "static_stickers").
     * @param null|array $location    (optional) Array containing lat, lng and horizontalAccuracy.
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

        $request = $this->request('creatives/assets/')
            ->addPost('type', $stickerType);

        if (!is_null($location)) {
            $request
                ->addPost('lat', $location['lat'])
                ->addPost('lng', $location['lat'])
                ->addPost('horizontalAccuracy', $location['horizontalAccuracy']);
        }

        $request->getResponse(new Response\StickerAssetsResponse());
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
