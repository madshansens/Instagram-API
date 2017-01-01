<?php

namespace InstagramAPI;

class Instagram
{
    public static $instance;
    public $username; // Instagram username
    public $password; // Instagram password
    public $debug;    // Debug
    public $truncatedDebug;

    public $uuid;               // UUID
    public $device_id;          // Device ID
    public $username_id;        // Username ID
    public $token;              // _csrftoken
    public $isLoggedIn = false; // Session status
    public $rank_token;         // Rank token

    public $http;
    public $settings;

    public $settingsAdopter = ['type'     => 'file',
        'path'                            => __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR, ]; // File | Mysql

    /*
    // Settings for mysql storage
    public $settingsAdopter         = array(
    "type"       => "mysql",
    "username"   => "",
    "password"   => "",
    "host"       => "",
    "database"   => "");
    */

    public $proxy = null;     // Full Proxy
    public $proxyHost = null; // Proxy Host and Port
    public $proxyAuth = null; // Proxy User and Pass

    /**
     * Default class constructor.
     *
     * @param $debug Debug on or off, false by default
     */
    public function __construct($debug = false, $truncatedDebug = false)
    {
        self::$instance = $this;
        $this->mapper = new \JsonMapper();
        $this->debug = $debug;
        $this->truncatedDebug = $truncatedDebug;
    }

    /**
     * Set the user. Manage multiple accounts.
     *
     * @param string $username Your Instagram username
     * @param string $password Your Instagram password
     */
    public function setUser($username, $password)
    {
        $this->device_id = SignatureUtils::generateDeviceId(md5($username.$password));
        $this->settings = new SettingsAdapter($this->settingsAdopter, $username);
        $this->checkSettings($username);
        $this->http = new HttpInterface($this);

        $this->username = $username;
        $this->password = $password;

        $this->uuid = SignatureUtils::generateUUID(true);
        if ($this->settings->isLogged()) {
            $this->isLoggedIn = true;
            $this->username_id = $this->settings->get('username_id');
            $this->rank_token = $this->username_id.'_'.$this->uuid;
            $this->token = $this->settings->get('token');
        } else {
            $this->isLoggedIn = false;
        }
    }

    protected function checkSettings($username)
    {
        if ($this->settings->get('version') == null) {
            $this->settings->set('version', Constants::VERSION);
        }

        if (($this->settings->get('user_agent') == null) || (version_compare($this->settings->get('version'), Constants::VERSION) == -1)) {
            $userAgent = new UserAgent($this);
            $ua = $userAgent->buildUserAgent();
            $this->settings->set('version', Constants::VERSION);
            $this->settings->set('user_agent', $ua);
        }
    }

    /**
     * Set the proxy.
     *
     * @param string $ip       Ip/hostname of proxy
     * @param int    $port     Port of proxy
     * @param string $username Username for proxy
     * @param string $password Password for proxy
     *
     * @throws InstagramException
     */
    public function setProxy($host, $port = null, $username = null, $password = null)
    {
        // no check needed we will give exception on curl if any data wrong / lastwisher
        // data assigned to http for easier use
        $this->http->proxy['host'] = $host;
        $this->http->proxy['port'] = $port;
        $this->http->proxy['username'] = $username;
        $this->http->proxy['password'] = $password;
    }

    /**
     * Login to Instagram.
     *
     * @param bool $force Force login to Instagram, this will create a new session
     *
     * @throws InstagramException
     *
     * @return ChallengeResponse|LoginResponse|ExploreResponse
     */
    public function login($force = false)
    {
        if (!$this->isLoggedIn || $force) {
            $this->syncFeatures(true);

            $response = $this->request('si/fetch_headers')
            ->requireLogin(true)
            ->addParams('challenge_type', 'signup')
            ->addParams('guid', SignatureUtils::generateUUID(false))
            ->getResponse(new ChallengeResponse(), true);

            if (!preg_match('#Set-Cookie: csrftoken=([^;]+)#', $response->getFullResponse()[0], $token)) {
                throw new InstagramException('Missing csfrtoken');
            }

            $response = $this->request('accounts/login/')
            ->requireLogin(true)
            ->addPost('phone_id', SignatureUtils::generateUUID(true))
            ->addPost('_csrftoken', $token[0])
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
            preg_match('#Set-Cookie: csrftoken=([^;]+)#', $response->getFullResponse()[0], $match);
            $this->token = $match[1];
            $this->settings->set('token', $this->token);

            $test = $this->syncFeatures();
            $this->autoCompleteUserList();
            $this->timelineFeed();
            $this->getRankedRecipients();
            $this->getRecentRecipients();
            $this->megaphoneLog();
            $this->getv2Inbox();
            $this->getRecentActivity();
            $this->getReelsTrayFeed();

            return $this->explore();
        }

        $check = $this->timelineFeed();
        if ($check->getMessage() == 'login_required') {
            $this->login(true);
        }
        $this->autoCompleteUserList();
        $this->getReelsTrayFeed();
        $this->getRankedRecipients();
        //push register
        $this->getRecentRecipients();
        //push register
        $this->megaphoneLog();
        $this->getv2Inbox();
        $this->getRecentActivity();

        return $this->explore();
    }

    /**
     * @param bool $prelogin
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
     * @return autoCompleteUserListResponse
     */
    public function autoCompleteUserList()
    {
        $this->request('friendships/autocomplete_user_list/')
        ->setCheckStatus(false)
        ->addParams('version', '2')
        ->getResponse(new autoCompleteUserListResponse());
    }

    /**
     * @param $gcmToken
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
            'phone_id'             => SignatureUtils::generateUUID(true),
            'device_type'          => 'android_mqtt',
            'device_token'         => $deviceToken,
            'is_main_push_channel' => true,
            '_csrftoken'           => $this->token,
            'users'                => $this->username_id,
        ]);

        return $this->http->request('push/register/?platform=10&device_type=android_mqtt', SignatureUtils::generateSignature($data))[1];
    }

    /**
     * Get timeline feed.
     *
     * @param $maxid
     *
     * @throws InstagramException
     *
     * @return TimelineFeedResponse
     */
    public function timelineFeed($maxId = null)
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
     * @return MegaphoneLogResponse
     */
    protected function megaphoneLog()
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
     * Pending Inbox.
     *
     * @throws InstagramException Pending Inbox Data
     *
     * @return PendingInboxResponse|void
     */
    public function getPendingInbox()
    {
        return $this->request('direct_v2/pending_inbox')->getResponse(new PendingInboxResponse());
    }

    /**
     * Ranked recipients.
     *
     * @throws InstagramException Ranked recipients Data
     *
     * @return RankedRecipientsResponse|void
     */
    public function getRankedRecipients()
    {
        return $this->request('direct_v2/ranked_recipients')
        ->addParams('show_threads', true)
        ->getResponse(new RankedRecipientsResponse());
    }

    /**
     * Recent recipients.
     *
     * @throws InstagramException Ranked recipients Data
     *
     * @return RecentRecipientsResponse|void
     */
    public function getRecentRecipients()
    {
        return $this->request('direct_share/recent_recipients/')
        ->getResponse(new RecentRecipientsResponse());
    }

    /**
     * Explore Tab.
     *
     * @throws InstagramException Explore data
     *
     * @return ExploreResponse|void
     */
    public function explore()
    {
        return $this->request('discover/explore/')->getResponse(new ExploreResponse());
    }

    /**
     * Home Channel.
     *
     * @throws InstagramException discoverChannel data
     *
     * @return DiscoverChannelResponse
     */
    public function discoverChannels()
    {
        return $this->request('discover/channels_home/')->getResponse(new DiscoverChannelsResponse());
    }

    /**
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
     * Logout of Instagram.
     *
     * @return bool
     *              Returns true if logged out correctly
     */
    public function logout()
    {
        return $this->request('accounts/logout/')->getResponse(new LogoutResponse());
    }

    /**
     * Upload photo to Instagram.
     *
     * @param string $photo         Path to your photo
     * @param string $caption       Caption to be included in your photo
     * @param null   $upload_id
     * @param null   $customPreview
     * @param null   $location
     * @param null   $filter
     *
     * @return Upload data
     */
    public function uploadPhoto($photo, $caption = null, $upload_id = null, $customPreview = null, $location = null, $filter = null)
    {
        return $this->http->uploadPhoto($photo, $caption, $upload_id, $customPreview, $location, $filter);
    }

    /**
     * @param $photo
     * @param null $caption
     * @param null $upload_id
     * @param null $customPreview
     */
    public function uploadPhotoStory($photo, $caption = null, $upload_id = null, $customPreview = null)
    {
        return $this->http->uploadPhoto($photo, $caption, $upload_id, $customPreview, null, null, true);
    }

    /**
     * Upload video to Instagram.
     *
     * @param $video Path to your video
     * @param null $caption       Caption to be included in your video
     * @param null $customPreview
     *
     * @return mixed
     */
    public function uploadVideo($video, $caption = null, $customPreview = null)
    {
        return $this->http->uploadVideo($video, $caption, $customPreview);
    }

    /**
     * @param $media_id
     * @param $recipients
     * @param null $text
     */
    public function direct_share($media_id, $recipients, $text = null)
    {
        $this->http->direct_share($media_id, $recipients, $text);
    }

    /**
     * Send direct message to user by inbox.
     *
     * @param array|int $recipients Users id
     * @param string    $text       Text message
     */
    public function direct_message($recipients, $text)
    {
        $this->http->direct_message($recipients, $text);
    }

    /**
     * Direct Thread Data.
     *
     * @param $threadId Thread Id
     *
     * @throws InstagramException Direct Thread Data
     *
     * @return array Direct Thread Data
     */
    // TODO : Missing Response
    public function directThread($threadId)
    {
        $directThread = $this->http->request("direct_v2/threads/$threadId/?")[1];

        if ($directThread['status'] != 'ok') {
            throw new InstagramException($directThread['message']."\n");
            return;
        }

        return $directThread;
    }

    /**
     * Direct Thread Action.
     *
     * @param string $threadId     Thread Id
     * @param string $threadAction Thread Action 'approve' OR 'decline' OR 'block'
     *
     * @return array Direct Thread Action Data
     */
    // TODO : Missing Response
    public function directThreadAction($threadId, $threadAction)
    {
        $data = json_encode([
            '_uuid'      => $this->uuid,
            '_uid'       => $this->username_id,
            '_csrftoken' => $this->token,
        ]);

        return $this->http->request("direct_v2/threads/$threadId/$threadAction/", SignatureUtils::generateSignature($data))[1];
    }

    /**
     * @param $upload_id
     * @param $video
     * @param string $caption
     * @param null   $customPreview
     *
     * @return ConfigureVideoResponse
     */
    public function configureVideo($upload_id, $video, $caption = '', $customPreview = null)
    {
        $this->uploadPhoto($video, $caption, $upload_id, $customPreview);

        $size = getimagesize($video)[0];

        return $this->request('media/configure/')
        ->addParams('video', 1)
        ->addPost('upload_id', $upload_id)
        ->addPost('source_type', '3')
        ->addPost('poster_frame_index', 0)
        ->addPost('length', 0.00)
        ->addPost('audio_muted', false)
        ->addPost('filter_type', '0')
        ->addPost('video_result', 'deprecated')
        ->addPost('clips', [
            'length'          => Utils::getSeconds($video),
            'source_type'     => '3',
            'camera_position' => 'back',
        ])
        ->addPost('extra', [
            'source_width'  => 960,
            'source_height' => 1280,
        ])
        ->addPost('device', [
            'manufacturer'    => $this->settings->get('manufacturer'),
            'model'           => $this->settings->get('model'),
            'android_version' => Constants::ANDROID_VERSION,
            'android_release' => Constants::ANDROID_RELEASE,
        ])
        ->addPost('_csrftoken', $this->token)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('caption', $caption)
        ->setReplacePost(['"length":0' => '"length":0.00'])
        ->getResponse(new ConfigureVideoResponse());
    }

    /**
     * @param $upload_id
     * @param $photo
     * @param string $caption
     * @param null   $location
     * @param null   $filter
     *
     * @return ConfigureResponse
     */
    public function configure($upload_id, $photo, $caption = '', $location = null, $filter = null)
    {
        $size = getimagesize($photo)[0];
        if (is_null($caption)) {
            $caption = '';
        }

        $requestData = $this->request('media/configure/')
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_folder', 'Instagram')
        ->addPost('source_type', 4)
        ->addPost('_uid', $this->username_id)
        ->addPost('_uuid', $this->uuid)
        ->addPost('caption', $caption)
        ->addPost('upload_id', $upload_id)
        ->addPost('device', [
            'manufacturer'    => $this->settings->get('manufacturer'),
            'model'           => $this->settings->get('model'),
            'android_version' => Constants::ANDROID_VERSION,
            'android_release' => Constants::ANDROID_RELEASE,
        ])
        ->addPost('edits', [
            'crop_original_size' => [$size, $size],
            'crop_center'        => [0, 0],
            'crop_zoom'          => 1,
        ])
        ->addPost('extra', [
            'source_width'  => $size,
            'source_height' => $size,
        ]);

        if (!is_null($location)) {
            $loc = [
                $location->getExternalIdSource().'_id'   => $location->getExternalId(),
                'name'                                   => $location->getName(),
                'lat'                                    => $location->getLatitude(),
                'lng'                                    => $location->getLongitude(),
                'address'                                => $location->getAddress(),
                'external_source'                        => $location->getExternalIdSource(),
            ];

            $requestData->addPost('location', json_encode($loc))
            ->addPost('geotag_enabled', true)
            ->addPost('media_latitude', $location->getLatitude())
            ->addPost('posting_latitude', $location->getLatitude())
            ->addPost('media_longitude', $location->getLongitude())
            ->addPost('posting_longitude', $location->getLongitude())
            ->addPost('altitude', mt_rand(10, 800));
        }

        if (!is_null($filter)) {
            $requestData->addPost('edits', ['filter_type' => Utils::getFilterCode($filter)]);
        }

        return $requestData->setReplacePost([
            '"crop_center":[0,0]'                   => '"crop_center":[0.0,-0.0]',
            '"crop_zoom":1'                         => '"crop_zoom":1.0',
            '"crop_original_size":'."[$size,$size]" => '"crop_original_size":'."[$size.0,$size.0]",
        ])
        ->getResponse(new ConfigureResponse());
    }

    /**
     * @param $upload_id
     * @param $photo
     *
     * @return ConfigureResponse
     */
    public function configureToReel($upload_id, $photo)
    {
        $size = getimagesize($photo)[0];

        return $this->request('media/configure_to_reel/')
        ->addPost('upload_id', $upload_id)
        ->addPost('source_type', 3)
        ->addPost('edits', [
            'crop_original_size' => [$size, $size],
            'crop_zoom'          => 1.3333334,
            'crop_center'        => [0.0, 0.0],
        ])
        ->addPost('extra', [
            'source_width'  => $size,
            'source_height' => $size,
        ])
        ->addPost('device', [
            'manufacturer'    => $this->settings->get('manufacturer'),
            'model'           => $this->settings->get('model'),
            'android_version' => Constants::ANDROID_VERSION,
            'android_release' => Constants::ANDROID_RELEASE,
        ])
        ->addPost('_csrftoken', $this->token)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->setReplacePost([
            '"crop_center":[0,0]' => '"crop_center":[0.0,0.0]',
        ])
        ->getResponse(new ConfigureResponse());
    }

    /**
     *  Edit media.
     *
     * @param $mediaId  Media id
     * @param string $captionText Caption text
     *
     * @return MediaResponse
     */
    public function editMedia($mediaId, $captionText = '', $usertags = null)
    {
        if (is_null($usertags)) {
            return $this->request("media/$mediaId/edit_media/")
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->username_id)
            ->addPost('_csrftoken', $this->token)
            ->addPost('caption_text', $captionText)
            ->getResponse(new EditMediaResponse());
        } else {
            return $this->request("media/$mediaId/edit_media/")
            ->addPost('_uuid', $this->uuid)
            ->addPost('_uid', $this->username_id)
            ->addPost('_csrftoken', $this->token)
            ->addPost('caption_text', $captionText)
            ->addPost('usertags', $usertags)
            ->getResponse(new EditMediaResponse());
        }
    }

    /**
     *  Tag User.
     *
     * @param string      $mediaId     Media id
     * @param string      $usernameId  Username id
     * @param array float $position    position relative to image where is placed the tag. Example: [0.4890625,0.6140625]
     * @param string      $captionText Caption text
     *
     * @return MediaResponse
     */
    public function tagUser($mediaId, $usernameId, $position, $captionText = '')
    {
        $usertag = '{"removed":[],"in":[{"position":['.$position[0].','.$position[1].'],"user_id":"'.$usernameId.'"}]}';

        return $this->editMedia($mediaId, $captionText, $usertag);
    }

    /**
     *  Untag User.
     *
     * @param string $mediaId     Media id
     * @param string $usernameId  Username id
     * @param string $captionText Caption text
     *
     * @return MediaResponse
     */
    public function untagUser($mediaId, $usernameId, $captionText = '')
    {
        $usertag = '{"removed":["'.$usernameId.'"],"in":[]}';

        return $this->editMedia($mediaId, $captionText, $usertag);
    }

    public function saveMedia($mediaId)
    {
        return $this->request("media/$mediaId/save/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(true)
        ->getResponse(new SaveAndUnsaveMedia());
    }

    public function unsaveMedia($mediaId)
    {
        return $this->request("media/$mediaId/unsave/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->setSignedPost(true)
        ->getResponse(new SaveAndUnsaveMedia());
    }

    /**
     *  Get Saved Feed.
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
     * Remove yourself from a tagged media.
     *
     * @param $mediaId
     *
     * @return MediaResponse
     */
    public function removeSelftag($mediaId)
    {
        return $this->request("usertags/$mediaId/remove/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new MediaResponse());
    }

    /**
     * Get media info.
     *
     * @param $mediaId
     *
     * @return MediaInfoResponse
     */
    public function mediaInfo($mediaId)
    {
        return $this->request("media/$mediaId/info/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new MediaInfoResponse());
    }

    /**
     * Delete photo or video.
     *
     * @param $mediaId
     *
     * @return mixed
     */
    public function deleteMedia($mediaId)
    {
        return $this->request("media/$mediaId/delete/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new MediaDeleteResponse());
    }

    /**
     * Comment media.
     *
     * @param $mediaId
     * @param $commentText
     *
     * @return CommentResponse
     */
    public function comment($mediaId, $commentText)
    {
        return $this->request("media/$mediaId/comment/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('comment_text', $commentText)
        ->getResponse(new CommentResponse());
    }

    /**
     * Delete Comment.
     *
     * @param string $mediaId
     *                          Media ID
     * @param string $commentId
     *                          Comment ID
     *
     * @return array
     *               Delete comment data
     */
    public function deleteComment($mediaId, $commentId)
    {
        return $this->request("media/$mediaId/comment/$commentId/delete/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new DeleteCommentResponse());
    }

    /**
     * Delete Comment Bulk.
     *
     * @param string $mediaId
     *                           Media id
     * @param string $commentIds
     *                           List of comments to delete
     *
     * @return array
     *               Delete Comment Bulk Data
     */
    public function deleteCommentsBulk($mediaId, $commentIds)
    {
        if (!is_array($commentIds)) {
            $commentIds = [$commentIds];
        }

        $string = [];
        foreach ($commentIds as $commentId) {
            $string[] = "$commentId";
        }

        $comment_ids_to_delete = implode(',', $string);

        return $this->request("media/$mediaId/comment/bulk_delete/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('comment_ids_to_delete', $comment_ids_to_delete)
        ->getResponse(new DeleteCommentResponse());
    }

    /**
     * Like Comment.
     *
     * @param string $commentId
     *
     * @return CommentLikeUnlikeResponse
     */
    public function likeComment($commentId)
    {
        return $this->request("media/$commentId/comment_like/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new CommentLikeUnlikeResponse());
    }

    /**
     * Unlike Comment.
     *
     * @param string $commentId
     *
     * @return CommentLikeUnlikeResponse
     */
    public function unlikeComment($commentId)
    {
        return $this->request("media/$commentId/comment_unlike/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new CommentLikeUnlikeResponse());
    }

    /**
     * Sets account to public.
     *
     * @param string $photo
     *                      Path to photo
     */
    public function changeProfilePicture($photo)
    {
        return new ProfileResponse($this->http->changeProfilePicture($photo));
    }

    /**
     * Remove profile picture.
     *
     * @return array
     *               status request data
     */
    public function removeProfilePicture()
    {
        return $this->request('accounts/remove_profile_picture/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new ProfileResponse());
    }

    /**
     * Sets account to private.
     *
     * @return array
     *               status request data
     */
    public function setPrivateAccount()
    {
        return $this->request('accounts/set_private/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new ProfileResponse());
    }

    /**
     * Sets account to public.
     *
     * @return array
     *               status request data
     */
    public function setPublicAccount()
    {
        return $this->request('accounts/set_public/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new ProfileResponse());
    }

    /**
     * Get personal profile data.
     *
     * @return ProfileResponse
     */
    public function getProfileData()
    {
        return $this->request('accounts/current_user/')
        ->addParams('edit', true)
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new ProfileResponse());
    }

    /**
     * Edit profile.
     *
     * @param string $url        Url - website. "" for nothing
     * @param string $phone      Phone number. "" for nothing
     * @param string $first_name Name. "" for nothing
     * @param string $email      Email. Required
     * @param int    $gender     Gender. male = 1 , female = 0
     *
     * @return ProfileResponse edit profile data
     */
    public function editProfile($url, $phone, $first_name, $biography, $email, $gender)
    {
        return $this->request('accounts/edit_profile/')
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('external_url', $url)
        ->addPost('phone_number', $phone)
        ->addPost('username', $this->username)
        ->addPost('first_name', $first_name)
        ->addPost('biography', $biography)
        ->addPost('email', $email)
        ->addPost('gender', $gender)
        ->getResponse(new ProfileResponse());
    }

    /**
     * Change Password.
     *
     * @param string $oldPassword Old Password
     * @param string $newPassword New Password
     *
     * @return array Change Password Data
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
     * Get username info.
     *
     * @param string $usernameId Username id
     *
     * @return UsernameInfoResponse Username data
     */
    public function getUsernameInfo($usernameId)
    {
        return $this->request("users/$usernameId/info/")->getResponse(new UsernameInfoResponse());
    }

    /**
     * Get self username info.
     *
     * @return UsernameInfoResponse Username data
     */
    public function getSelfUsernameInfo()
    {
        return $this->getUsernameInfo($this->username_id);
    }

    /**
     * Get recent activity.
     *
     * @throws InstagramException
     *
     * @return mixed Recent activity data
     */
    public function getRecentActivity()
    {
        return $this->request('news/inbox/')->addParams('activity_module', 'all')->getResponse(new ActivityNewsResponse());
    }

    /**
     * Get recent activity from accounts followed.
     *
     * @throws InstagramException
     *
     * @return mixed Recent activity data of follows
     */
    public function getFollowingRecentActivity($maxid = null)
    {
        $activity = $this->request('news/');
        if (!is_null($maxid)) {
            $activity->addParams('max_id', $maxid);
        }

        return $activity->getResponse(new FollowingRecentActivityResponse());
    }

    /**
     * I dont know this yet.
     *
     * @throws InstagramException
     *
     * @return V2InboxResponse v2 inbox data
     */
    public function getv2Inbox()
    {
        return $this->request('direct_v2/inbox/')->getResponse(new V2InboxResponse());
    }

    /**
     * Get user tags.
     *
     * @param string $usernameId
     *
     * @throws InstagramException
     *
     * @return UsertagsResponse user tags data
     */
    public function getUserTags($usernameId)
    {
        return $this->request("usertags/$usernameId/feed/")
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', 'true')
        ->getResponse(new UsertagsResponse());
    }

    /**
     * Get self user tags.
     *
     * @return UsertagsResponse self user tags data
     */
    public function getSelfUserTags()
    {
        return $this->getUserTags($this->username_id);
    }

    /**
     * Get media likers.
     *
     * @param string $mediaId
     *
     * @throws InstagramException
     *
     * @return MediaLikersResponse
     */
    public function getMediaLikers($mediaId)
    {
        return $this->request("media/$mediaId/likers/")->getResponse(new MediaLikersResponse());
    }

    /**
     * Get user locations media.
     *
     * @param string $usernameId Username id
     *
     * @throws InstagramException
     *
     * @return array Geo Media data
     */
    public function getGeoMedia($usernameId)
    {
        return $this->request("maps/user/$usernameId/")->getResponse(new GeoMediaResponse());
    }

    /**
     * Get self user locations media.
     *
     * @return array Geo Media data
     */
    public function getSelfGeoMedia()
    {
        return $this->getGeoMedia($this->username_id);
    }

    /**
     * @param $latitude
     * @param $longitude
     * @param null $query
     *
     * @throws InstagramException
     *
     * @return LocationResponse|void
     */
    public function searchLocation($latitude, $longitude, $query = null)
    {
        $locations = $this->request('location_search/')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('latitude', $latitude)
        ->addParams('longitude', $longitude);

        if (!is_null($query)) {
            $locations->addParams('timestamp', time());
        } else {
            $locations->addParams('search_query', $query);
        }

        return $locations->getResponse(new LocationResponse());
    }

    /**
     * facebook user search.
     *
     * @param string $query
     *
     * @throws InstagramException
     *
     * @return array query data
     */
    public function fbUserSearch($query)
    {
        $query = rawurlencode($query);

        return $this->request('fbsearch/topsearch/')
        ->addParams('context', 'blended')
        ->addParams('query', $query)
        ->addParams('rank_token', $this->rank_token)
        ->getResponse(new FBSearchResponse());
    }

    /**
     * Search users.
     *
     * @param string $query
     *
     * @throws InstagramException
     *
     * @return array query data
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
     * Search exact username.
     *
     * @param string usernameName username as STRING not an id
     *
     * @throws InstagramException
     *
     * @return UsernameInfoResponse query data
     */
    public function searchUsername($usernameName)
    {
        return $this->request("users/$usernameName/usernameinfo/")->getResponse(new UsernameInfoResponse());
    }

    /**
     * @param $username
     *
     * @return mixed
     */
    public function getUsernameId($username)
    {
        return $this->searchUsername($username)->getUser()->getPk();
    }

    /**
     * Search users using addres book.
     *
     * @param array $contacts
     *
     * @return array
     *               query data
     */
    public function syncFromAdressBook($contacts)
    {
        return $this->request('address_book/link/?include=extra_display_name,thumbnails')
        ->setSignedPost(false)
        ->addPost('contacts', json_encode($contacts, true))
        ->getResponse(new AddressBookResponse());
    }

    /**
     * Get related tags.
     *
     * @param string $tag
     *
     * @throws InstagramException
     *
     * @return array query data
     */
    public function getTagRelated($tag)
    {
        return $this->request("tags/$tag/related")
        ->addParams('visited', urlencode('[{"id":"'.$tag.'","type":"hashtag"}]'))
        ->addParams('related_types', urlencode('["hashtag"]'))
        ->getResponse(new TagRelatedResponse());
    }

    /**
     * Get tag info: media_count.
     *
     * @param string $tag
     *
     * @throws InstagramException
     *
     * @return TagInfoResponse
     */
    public function getTagInfo($tag)
    {
        return $this->request("tags/$tag/info")
        ->getResponse(new TagInfoResponse());
    }

    /**
     * @throws InstagramException
     *
     * @return ReelsTrayFeedResponse|void
     */
    public function getReelsTrayFeed()
    {
        return $this->request('feed/reels_tray/')->getResponse(new ReelsTrayFeedResponse());
    }

    /**
     * Get a user's Story Feed.
     *
     * @return UserStoryFeedResponse
     */
    public function getUserStoryFeed($userId)
    {
        return $this->request("feed/user/$userId/story/")
        ->getResponse(new UserStoryFeedResponse());
    }

    /**
     * Get multiple users' story reels.
     *
     * @param array $userList List of User IDs
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
     * Get user feed.
     *
     * @param string $usernameId   Username id
     * @param null   $maxid        Max Id
     * @param null   $minTimestamp Min timestamp
     *
     * @throws InstagramException
     *
     * @return UserFeedResponse User feed data
     */
    public function getUserFeed($usernameId, $maxid = null, $minTimestamp = null)
    {
        return $this->request("feed/user/$usernameId/")
        ->addParams('rank_token', $this->rank_token)
        ->addParams('ranked_content', 'true')
        ->addParams('max_id', (!is_null($maxid) ? $maxid : ''))
        ->addParams('min_timestamp', (!is_null($minTimestamp) ? $minTimestamp : ''))
        ->getResponse(new UserFeedResponse());
    }

    /**
     * Get hashtag feed.
     *
     * @param string $hashtagString Hashtag string, not including the #
     *
     * @throws InstagramException
     *
     * @return array Hashtag feed data
     */
    public function getHashtagFeed($hashtagString, $maxid = null)
    {
        $hashtagFeed = $this->request("feed/tag/$hashtagString/");
        if (!is_null($maxid)) {
            $hashtagFeed->addParams('max_id', $maxid);
        }

        return $hashtagFeed->getResponse(new TagFeedResponse());
    }

    /**
     * Get locations.
     *
     * @param string $query search query
     *
     * @throws InstagramException
     *
     * @return array Location location data
     */
    public function searchFBLocation($query)
    {
        $query = urlencode($query);

        return $this->request('fbsearch/places/')
        ->addParams('rank_token', $this->rank_token)
        ->addParams('query', $query)
        ->getResponse(new FBLocationResponse());
    }

    /**
     * Get location feed.
     *
     * @param string $locationId location id
     *
     * @throws InstagramException
     *
     * @return array Location feed data
     */
    public function getLocationFeed($locationId, $maxid = null)
    {
        $locationFeed = $this->request("feed/location/$locationId/");
        if (!is_null($maxid)) {
            $locationFeed->addParams('max_id', $maxid);
        }

        return $locationFeed->getResponse(new LocationFeedResponse());
    }

    /**
     * Get self user feed.
     *
     * @return UserFeedResponse User feed data
     */
    public function getSelfUserFeed($max_id = null, $minTimestamp = null)
    {
        return $this->getUserFeed($this->username_id, $max_id, $minTimestamp);
    }

    /**
     * Get popular feed.
     *
     * @throws InstagramException
     *
     * @return array popular feed data
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
     * Get user followings.
     *
     * @param string $usernameId Username id
     *
     * @return FollowerAndFollowingResponse followers data
     */
    public function getUserFollowings($usernameId, $maxid = null)
    {
        $requestData = $this->request("friendships/$usernameId/following/")
        ->addParams('rank_token', $this->rank_token);
        if (!is_null($maxid)) {
            $requestData->addParams('max_id', $maxid);
        }

        return $requestData->getResponse(new FollowerAndFollowingResponse());
    }

    /**
     * Get user followers.
     *
     * @param string $usernameId Username id
     *
     * @return FollowerAndFollowingResponse followers data
     */
    public function getUserFollowers($usernameId, $maxid = null)
    {
        $requestData = $this->request("friendships/$usernameId/followers/")
        ->addParams('rank_token', $this->rank_token);
        if (!is_null($maxid)) {
            $requestData->addParams('max_id', $maxid);
        }

        return $requestData->getResponse(new FollowerAndFollowingResponse());
    }

    /**
     * Get self user followers.
     *
     * @return FollowerAndFollowingResponse followers data
     */
    public function getSelfUserFollowers($max_id = null)
    {
        return $this->getUserFollowers($this->username_id, $max_id);
    }

    /**
     * Get self users we are following.
     *
     * @return FollowerAndFollowingResponse users we are following data
     */
    public function getSelfUsersFollowing($max_id = null)
    {
        return $this->getUserFollowings($this->username_id, $max_id);
    }

    /**
     * Like photo or video.
     *
     * @param string $mediaId Media id
     *
     * @return array status request
     */
    public function like($mediaId)
    {
        return $this->request("media/$mediaId/like/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('media_id', $mediaId)
        ->getResponse(new Response());
    }

    /**
     * Unlike photo or video.
     *
     * @param string $mediaId Media id
     *
     * @return array status request
     */
    public function unlike($mediaId)
    {
        return $this->request("media/$mediaId/unlike/")
         ->addPost('_uuid', $this->uuid)
         ->addPost('_uid', $this->username_id)
         ->addPost('_csrftoken', $this->token)
         ->addPost('media_id', $mediaId)
         ->getResponse(new Response());
    }

    /**
     * Get media comments.
     *
     * @param string $mediaId Media id
     *
     * @return MediaCommentsResponse Media comments data
     */
    public function getMediaComments($mediaId, $maxid = null)
    {
        return $this->request("media/$mediaId/comments/")
        ->addParams('ig_sig_key_version', Constants::SIG_KEY_VERSION)
        ->addParams('max_id', $maxid)
        ->getResponse(new MediaCommentsResponse());
    }

    /**
     * Set name and phone (Optional).
     *
     * @param string $name
     * @param string $phone
     *
     * @return array Set status data
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
     * Get direct share.
     *
     * @return array Direct share data
     */
    public function getDirectShare()
    {
        return $this->request('direct_share/inbox/?')
        ->getResponse(new DirectShareInboxResponse());
    }

    /**
     * Backups all your uploaded photos and videos :).
     */
    public function backup()
    {
        $nextUploadMaxId = null;
        do {
            $myUploads = $this->getSelfUserFeed($nextUploadMaxId);

            $backupMainFolder = $this->settingsAdopter['path'].$this->username.'/backup/';
            $backupFolder = $backupMainFolder.'/'.date('Y-m-d').'/';

            if (!is_dir($backupMainFolder)) {
                mkdir($backupMainFolder);
            }
            if (!is_dir($backupFolder)) {
                mkdir($backupFolder);
            }

            foreach ($myUploads->getItems() as $item) {
                if ($item->media_type == Item::PHOTO) {
                    $itemUrl = $item->getImageVersions2()->candidates[0]->getUrl();
                } else {
                    $itemUrl = $item->getVideoVersions()[0]->getUrl();
                }
                $fileExtension = pathinfo(parse_url($itemUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                copy($itemUrl, $backupFolder.$item->getId().'.'.$fileExtension);
            }
        } while (!is_null($nextUploadMaxId = $myUploads->getNextMaxId()));
    }

    /**
     * Follow.
     *
     * @param string $userId
     *
     * @return array Friendship status data
     */
    public function follow($userId)
    {
        return $this->request("friendships/create/$userId/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new FriendshipResponse());
    }

    /**
     * Unfollow.
     *
     * @param string $userId
     *
     * @return array Friendship status data
     */
    public function unfollow($userId)
    {
        return $this->request("friendships/destroy/$userId/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new FriendshipResponse());
    }

    /**
     * Get suggested users.
     *
     * @param string $userId
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
     * Block.
     *
     * @param string $userId
     *
     * @return array Friendship status data
     */
    public function block($userId)
    {
        return $this->request("friendships/block/$userId/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new FriendshipResponse());
    }

    /**
     * Unblock.
     *
     * @param string $userId
     *
     * @return array Friendship status data
     */
    public function unblock($userId)
    {
        return $this->request("friendships/unblock/$userId/")
        ->addPost('_uuid', $this->uuid)
        ->addPost('_uid', $this->username_id)
        ->addPost('_csrftoken', $this->token)
        ->addPost('user_id', $userId)
        ->getResponse(new FriendshipResponse());
    }

    /**
     * Show User Friendship.
     *
     * @param string $userId
     *
     * @return FriendshipStatus relationship data
     */
    public function userFriendship($userId)
    {
        return $this->request("friendships/show/$userId/")->getResponse(new FriendshipStatus());
    }

    /**
     * Show Multiple Users Friendship.
     *
     * @param string $userId
     *
     * @return FriendshipsShowManyResponse
     */
    public function usersFriendship($userList)
    {
        return $this->request('friendships/show_many/')
        ->setSignedPost(false)
        ->addPost('_uuid', $this->uuid)
        ->addPost('user_ids', implode(',', $userList))
        ->addPost('_csrftoken', $this->token)
        ->getResponse(new FriendshipsShowManyResponse());
    }

    /**
     * Get liked media.
     *
     * @return array Liked media data
     */
    public function getLikedMedia($maxid = null)
    {
        return $this->request('feed/liked/?'.(!is_null($maxid) ? 'max_id='.$maxid.'&' : ''))
        ->getResponse(new LikeFeedResponse());
    }

    public function verifyPeer($enable)
    {
        $this->http->verifyPeer($enable);
    }

    public function verifyHost($enable)
    {
        $this->http->verifyHost($enable);
    }

    /**
     * Search tags.
     *
     * @param string $query
     *
     * @throws InstagramException
     *
     * @return array query data
     */
    public function searchTags($query)
    {
        return $this->request('tags/search/')
        ->addParams('is_typeahead', true)
        ->addParams('q', $query)
        ->addParams('rank_token', $this->rank_token)
        ->getResponse(new SearchTagResponse());
    }

    // just for easy typing for earlier php versions
    public function request($url)
    {
        return new Request($url);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

/**
 * Bridge between http object & mapper & response.
 */
class Request
{
    protected $params = [];
    protected $posts = [];
    protected $requireLogin = false;
    protected $floodWait = false;
    protected $checkStatus = true;
    protected $signedPost = true;
    protected $replacePost = [];

    public function __construct($url)
    {
        $this->url = $url;

        return $this;
    }

    public function addParams($key, $value)
    {
        if ($value === true) {
            $value = 'true';
        }
        $this->params[$key] = $value;

        return $this;
    }

    public function addPost($key, $value)
    {
        $this->posts[$key] = $value;

        return $this;
    }

    public function requireLogin($requireLogin = false)
    {
        $this->requireLogin = $requireLogin;

        return $this;
    }

    public function setFloodWait($floodWait = false)
    {
        $this->floodWait = $floodWait;

        return $this;
    }

    public function setCheckStatus($checkStatus = true)
    {
        $this->checkStatus = $checkStatus;

        return $this;
    }

    public function setSignedPost($signedPost = true)
    {
        $this->signedPost = $signedPost;

        return $this;
    }

    public function setReplacePost($replace = [])
    {
        $this->replacePost = $replace;

        return $this;
    }

    public function getResponse($obj, $includeHeader = false)
    {
        $instagramObj = Instagram::getInstance();

        if ($this->params) {
            $endPoint = $this->url.'?'.http_build_query($this->params);
        } else {
            $endPoint = $this->url;
        }
        if ($this->posts) {
            if ($this->signedPost) {
                $post = SignatureUtils::generateSignature(json_encode($this->posts));
            } else {
                $post = http_build_query($this->posts);
            }
        } else {
            $post = null;
        }
        if ($this->replacePost) {
            $post = str_replace(array_keys($this->replacePost), array_values($this->replacePost), $post);
        }

        $response = $instagramObj->http->request($endPoint, $post, $this->requireLogin, $this->floodWait, false);

        $mapper = new \JsonMapper();
        $mapper->bStrictNullTypes = false;
        if (isset($_GET['debug'])) {
            $mapper->bExceptionOnUndefinedProperty = true;
        }
        if (is_null($response[1])) {
            throw new InstagramException('No response from server, connection or configure error');
        }

        $responseObject = $mapper->map($response[1], $obj);

        if ($this->checkStatus && !$responseObject->isOk()) {
            throw new InstagramException(get_class($obj).' : '.$responseObject->getMessage());
        }
        if ($includeHeader) {
            $responseObject->setFullResponse($response);
        } else {
            $responseObject->setFullResponse($response[1]);
        }

        return $responseObject;
    }
}
