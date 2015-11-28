<?php
require_once 'InstagramException.php';

class Instagram {

  const API_URL         = 'https://i.instagram.com/api/v1/';
  const USER_AGENT      = 'Instagram 7.10.0 Android (23/6.0; 515dpi; 1440x2416; huawei/google; Nexus 6P; angler; angler; en_US)';
  const IG_SIG_KEY      = 'c1c7d84501d2f0df05c378f5efb9120909ecfb39dff5494aa361ec0deadb509a';
  const SIG_KEY_VERSION = '4';


  protected $username;            // Instagram username
  protected $password;            // Instagram password
  protected $debug;               // Debug

  protected $uuid;                // UUID
  protected $device_id;           // Device ID
  protected $username_id;         // Username ID
  protected $token;               // _csrftoken
  protected $isLoggedIn = false;  // Session status
  protected $rank_token;          // Rank token

  /**
    * Default class constructor.
    *
    * @param string $username
    *   Your Instagram username.
    * @param string $password
    *   Your Instagram password.
    * @param $debug
    *   Debug on or off, false by default.
    * @param $IGDataPath
    *  Default folder to store data, you can change it.
    */
  public function Instagram($username, $password, $debug = false, $IGDataPath = null)
  {
    $this->username = $username;
    $this->password = $password;
    $this->debug    = $debug;

    $this->uuid      = $this->generateUUID(true);
    $this->device_id = 'android-' . str_split(md5(rand(1000, 9999)), 16)[rand(0, 1)];

    if (!is_null($IGDataPath))
      $this->IGDataPath = $IGDataPath;
    else
      $this->IGDataPath = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;

    if (file_exists($this->IGDataPath . 'cookies.dat'))
    {
      $this->isLoggedIn = true;
      $this->username_id = file_get_contents($this->IGDataPath . 'userId.dat');
      $this->rank_token = $this->username_id . "_" . $this->uuid;
    }
  }

  /**
  * Login to Instagram
  *
  * @return array
  *    Login data
  */
  public function login()
  {
    if (!$this->isLoggedIn)
    {
      $fetch = $this->request('si/fetch_headers/?challenge_type=signup&guid=' . $this->generateUUID(false));
      preg_match('#Set-Cookie: csrftoken=([^;]+)#', $fetch[0], $token);

      $data = array(
          'device_id' => $this->device_id,
          'guid'      => $this->uuid,
          'username'  => $this->username,
          'password'  => $this->password,
          'csrftoken' => $token[1],
          'login_attempt_count' => '0'
      );

      $login = $this->request('accounts/login/', $this->generateSignature(json_encode($data)));

      if ($login[1]['status'] == 'fail')
      {
        throw new InstagramException($login[1]['message']);
        return;
      }

      $this->isLoggedIn = true;
      $this->username_id = $login[1]['logged_in_user']['pk'];
      file_put_contents($this->IGDataPath . 'userId.dat', $this->username_id);
      $this->rank_token = $this->username_id . "_" . $this->uuid;
      preg_match('#Set-Cookie: csrftoken=([^;]+)#', $login[0], $match);
      $this->token = $match[1];

      return $login[1];
    }
  }

  /**
  * Login to Instagram
  *
  * @return bool
  *    Returns true if logged out correctly
  */
  public function logout()
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $logout = $this->request('accounts/logout/');

    if ($logout == 'ok')
      return true;
    else
      return false;
  }

  /**
  * Upload photo to Instagram
  *
  * @param string $photo
  *   Path to your photo
  * @param string $caption
  *   Caption to be included in your photo.
  *
  * @return array
  *   Upload data
  */
	public function uploadPhoto($photo, $caption = null)
  {

    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

		$endpoint = self::API_URL. 'upload/photo/';
		$boundary = $this->uuid;
		$bodies = [
			[
				'type' => 'form-data',
				'name' => 'upload_id',
				'data' => round(microtime(true)*1000)
			],
			[
				'type' => 'form-data',
				'name' => '_uuid',
				'data' => $this->uuid
			],
			[
				'type' => 'form-data',
				'name' => '_csrftoken',
				'data' => $this->token
			]/*,
			[
				"type"=>"form-data",
				"name"=>"image_compression"
			//	"data"=>"Your JSON DATA COMPRESSION HERE"
			]*/,
			[
				'type' => 'form-data',
				'name' => 'photo',
				'data' => file_get_contents($photo),
				'filename' => basename($photo),
				'headers' =>
        [
					'Content-type: application/octet-stream'
				]
			]
		];

		$data = $this->buildBody($bodies,$boundary);
		$headers = [
				'Proxy-Connection: keep-alive',
				'Connection: keep-alive',
				'Accept: */*',
				'Content-type: multipart/form-data; boundary='.$boundary,
				'Accept-Language: en-en',
				'Accept-Encoding: gzip, deflate',
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . 'cookies.dat');
		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . 'cookies.dat');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

		$resp       = curl_exec($ch);
		$header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$header     = substr($resp, 0, $header_len);
		$upload     = json_decode(substr($resp, $header_len), true);

		curl_close($ch);

    if ($upload['status'] == 'fail')
    {
      throw new InstagramException($upload['message']);
      return;
    }

    $configure = $this->configure($upload['upload_id'], $photo, $caption);

		return $configure[1];
	}

  protected function configure($upload_id, $photo, $caption = null)
  {

    $size = getimagesize($photo)[0];

    $post = array(
      'caption'     => $caption,
      'upload_id'   => $upload_id,
      'source_type' => 3,
      'edits'       =>
       array(
          'crop_zoom'          => 1.0000000,
          'crop_center'        => array(0.0, -0.0),
          'crop_original_size' => array($size, $size),
          'black_pixels_ratio' => 0
       ),
       'device'      =>
       array(
          'manufacturer'    => 'asus',
          'model'           => 'Nexus 7',
          'android_version' => 22,
          'android_release' => '5.1'
       ),
       '_csrftoken'  => $this->token,
       '_uuid'       => $this->uuid,
       '_uid'        => $this->username_id
     );

      return $this->request('media/configure/', $this->generateSignature(json_encode($post)));
  }

  public function changeProfilePicture($photo)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    if (is_null($photo))
    {
      echo "Photo not valid\n\n";
      return;
    }

    $uData = json_encode(array(
      '_csrftoken' => $this->token,
      '_uuid'      => $this->uuid,
      '_uid'       => $this->username_id
    ));

    $endpoint = self::API_URL. 'accounts/change_profile_picture/';
    $boundary = $this->uuid;
    $bodies = [
      [
        'type' => 'form-data',
        'name' => 'ig_sig_key_version',
        'data' => self::SIG_KEY_VERSION
      ],
      [
        'type' => 'form-data',
        'name' => 'signed_body',
        'data' => hash_hmac('sha256', $uData, self::IG_SIG_KEY) . $uData
      ],
      [
        'type' => 'form-data',
        'name' => 'profile_pic',
        'data' => file_get_contents($photo),
        'filename' => 'profile_pic',
        'headers' =>
        [
          'Content-type: application/octet-stream',
          'Content-Transfer-Encoding: binary'
        ]
      ]
    ];

    $data = $this->buildBody($bodies,$boundary);
    $headers = [
        'Proxy-Connection: keep-alive',
        'Connection: keep-alive',
        'Accept: */*',
        'Content-type: multipart/form-data; boundary='.$boundary,
        'Accept-Language: en-en',
        'Accept-Encoding: gzip, deflate',
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $endpoint);
    curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . 'cookies.dat');
    curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . 'cookies.dat');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $resp       = curl_exec($ch);
    $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header     = substr($resp, 0, $header_len);
    $upload     = json_decode(substr($resp, $header_len), true);

    curl_close($ch);
  }

  public function removeProfilePicture()
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $data = json_encode(array(
        '_uuid'  => $this->uuid,
        '_uid'   => $this->username_id,
        '_csrftoken' => $this->token
    ));

    return $this->request("accounts/remove_profile_picture/", $this->generateSignature($data))[1];
  }

  public function setPrivateAccount()
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $data = json_encode(array(
        '_uuid'  => $this->uuid,
        '_uid'   => $this->username_id,
        '_csrftoken' => $this->token
    ));

    return $this->request("accounts/set_private/", $this->generateSignature($data))[1];
  }

  public function setPublicAccount()
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $data = json_encode(array(
        '_uuid'  => $this->uuid,
        '_uid'   => $this->username_id,
        '_csrftoken' => $this->token
    ));

    return $this->request("accounts/set_public/", $this->generateSignature($data))[1];
  }


  /**
  * Get username info
  *
  * @param string $usernameId
  *   Username id
  *
  * @return array
  *   Username data
  */
  public function getUsernameInfo($usernameId)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    return $this->request("users/$usernameId/info/")[1];
  }

  /**
  * Get self username info
  *
  * @return array
  *   Username data
  */
  public function getSelfUsernameInfo()
  {
    return $this->getUsernameInfo($this->username_id);
  }

  /**
  * Get recent activity
  *
  * @return array
  *   Recent activity data
  */
  public function getRecentActivity()
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $activity = $this->request("news/inbox/?")[1];

    if ($activity['status'] != 'ok')
    {
      throw new InstagramException($activity['message'] . "\n");
      return;
    }

    return $activity;
  }

  /**
  * I dont know this yet
  *
  * @return array
  *   v2 inbox data
  */
  public function getv2Inbox()
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $inbox = $this->request("direct_v2/inbox/?")[1];

    if ($inbox['status'] != 'ok')
    {
      throw new InstagramException($inbox['message'] . "\n");
      return;
    }

    return $inbox;
  }

  /**
  * Get user tags
  *
  * @param String $usernameId
  *
  * @return array
  *   user tags data
  */
  public function getUserTags($usernameId)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $tags = $this->request("usertags/$usernameId/feed/?rank_token=$this->rank_token&ranked_content=true&")[1];

    if ($tags['status'] != 'ok')
    {
      throw new InstagramException($tags['message'] . "\n");
      return;
    }

    return $tags;
  }

  /**
  * Get self user tags
  *
  * @return array
  *   self user tags data
  */
  public function getSelfUserTags()
  {
    return $this->getUserTags($this->username_id);
  }

  /**
  * Get user locations media
  *
  * @param string $usernameId
  *   Username id
  *
  * @return array
  *   Geo Media data
  */
  public function getGeoMedia($usernameId)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $locations = $this->request("maps/user/$usernameId/")[1];

    if ($locations['status'] != 'ok')
    {
      throw new InstagramException($locations['message'] . "\n");
      return;
    }

    return $locations;
  }

  /**
  * Get self user locations media
  *
  * @return array
  *   Geo Media data
  */
  public function getSelfGeoMedia()
  {
    return $this->getGeoMedia($this->username_id);
  }

  /**
  * Search users
  *
  * @param string $query
  *
  * @return array
  *   query data
  */
  public function searchUsers($query)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $query = $this->request("fbsearch/topsearch/?context=blended&query=$query&rank_token=$this->rank_token")[1];

    if ($query['status'] != 'ok')
    {
      throw new InstagramException($query['message'] . "\n");
      return;
    }

    return $query;
  }

  /**
  * Get timeline data
  *
  * @return array
  *   timeline data
  */
  public function getTimeline()
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $timeline = $this->request("feed/timeline/?rank_token=$this->rank_token&ranked_content=true&")[1];

    if ($timeline['status'] != 'ok')
    {
      throw new InstagramException($timeline['message'] . "\n");
      return;
    }

    return $timeline;
  }

  /**
  * Get user feed
  *
  * @param String $usernameId
  *    Username id
  *
  * @return array
  *   User feed data
  */
  public function getUserFeed($usernameId)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $userFeed = $this->request("feed/user/$usernameId/?rank_token=$this->rank_token&ranked_content=true&")[1];

    if ($userFeed['status'] != 'ok')
    {
      throw new InstagramException($userFeed['message'] . "\n");
      return;
    }

    return $userFeed;
  }

  /**
  * Get self user feed
  *
  * @return array
  *   User feed data
  */
  public function getSelfUserFeed()
  {
    return $this->getUserFeed($this->username_id);
  }

  /**
  * Get popular feed
  *
  * @return array
  *   popular feed data
  */
  public function getPopularFeed()
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $popularFeed = $this->request("feed/popular/?people_teaser_supported=1&rank_token=$this->rank_token&ranked_content=true&")[1];

    if ($popularFeed['status'] != 'ok')
    {
      throw new InstagramException($popularFeed['message'] . "\n");
      return;
    }

    return $popularFeed;
  }

  /**
  * Get user followers
  *
  * @param String $usernameId
  *   Username id
  *
  * @return array
  *   followers data
  */
  public function getUserFollowers($usernameId)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    return $this->request("friendships/$usernameId/followers/?ig_sig_key_version=" . self::SIG_KEY_VERSION . "&rank_token=$this->rank_token")[1];
  }

  /**
  * Get self user followers
  *
  * @return array
  *   followers data
  */
  public function getSelfUserFollowers()
  {
    return $this->getUserFollowers($this->username_id);
  }

  /**
  * Like photo or video
  *
  * @param String $mediaId
  *   Media id
  *
  * @return array
  *   status request
  */
  public function like($mediaId)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $data = json_encode(array(
        '_uuid'  => $this->uuid,
        '_uid'   => $this->username_id,
        '_csrftoken' => $this->token,
        'media_id'   => $mediaId
    ));

    return $this->request("media/$mediaId/like/", $this->generateSignature($data))[1];
  }

  /**
  * Unlike photo or video
  *
  * @param String $mediaId
  *   Media id
  *
  * @return array
  *   status request
  */
  public function unlike($mediaId)
  {
    if (!$this->isLoggedIn)
    {
      throw new InstagramException("Not logged in\n");
      return;
    }

    $data = json_encode(array(
        '_uuid'  => $this->uuid,
        '_uid'   => $this->username_id,
        '_csrftoken' => $this->token,
        'media_id'   => $mediaId
    ));

    return $this->request("media/$mediaId/unlike/", $this->generateSignature($data))[1];
  }

  protected function generateSignature($data)
  {
    $hash = hash_hmac('sha256', $data, self::IG_SIG_KEY);

    return 'ig_sig_key_version=4&signed_body=' . $hash . '.' . urlencode($data);
  }

  protected function generateDeviceId()
  {
    return 'android-' . str_split(md5(rand(1000, 9999)), 16)[rand(0, 1)];
  }

  protected function generateUUID($type)
  {
    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    return $type ? $uuid : str_replace('-', '', $uuid);
  }

  protected function buildBody($bodies, $boundary)
  {
    $body = "";
    foreach($bodies as $b)
    {
      $body .= "--".$boundary."\r\n";
      $body .= "Content-Disposition: ".$b["type"]."; name=\"".$b["name"]."\"";
      if(isset($b["filename"]))
      {
        $ext = pathinfo($b["filename"], PATHINFO_EXTENSION);
        $body .= "; filename=\"".substr(bin2hex($b["filename"]),0,48).".".$ext."\"";
      }
      if(isset($b["headers"]) && is_array($b["headers"]))
      {
        foreach($b["headers"] as $header)
        {
          $body.= "\r\n".$header;
        }
      }

      $body.= "\r\n\r\n".$b["data"]."\r\n";
    }
    $body .= "--".$boundary."--";

    return $body;
  }

  protected function request($endpoint, $post = null) {

   $ch = curl_init();

   curl_setopt($ch, CURLOPT_URL, self::API_URL . $endpoint);
   curl_setopt($ch, CURLOPT_USERAGENT, self::USER_AGENT);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
   curl_setopt($ch, CURLOPT_HEADER, true);
   curl_setopt($ch, CURLOPT_VERBOSE, false);
   curl_setopt($ch, CURLOPT_COOKIEFILE, $this->IGDataPath . 'cookies.dat');
   curl_setopt($ch, CURLOPT_COOKIEJAR, $this->IGDataPath . 'cookies.dat');

   if ($post) {

   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

   }

   $resp       = curl_exec($ch);
   $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
   $header     = substr($resp, 0, $header_len);
   $body       = substr($resp, $header_len);

   curl_close($ch);

   if ($this->debug)
   {
     echo "REQUEST: $endpoint\n";
     if (!is_null($post))
     {
       if (!is_array($post))
        echo "DATA: $post\n";
     }
     echo "RESPONSE: $body\n\n";
   }

   return array($header, json_decode($body, true));

  }
}
