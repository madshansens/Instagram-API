<?php
require_once 'InstagramException.php';

class Instagram {

  const API_URL = 'https://instagram.com/api/v1/';

  protected $username; // Instagram username
  protected $password; // Instagram password
  protected $debug;    // Debug

  protected $agent;     // User agent
  protected $uuid;      // UUID
  protected $device_id; // Device ID

  public function Instagram($username, $password, $debug = false)
  {
    $this->username = $username;
    $this->password = $password;
    $this->debug    = $debug;

    $this->agent     = $this->generateUserAgent();
    $this->uuid      = $this->generateUUID();
    $this->device_id = 'android-' . $this->uuid;
  }

  public function login()
  {
    $data = array(
        'device_id' => $this->device_id,
        'guid'      => $this->uuid,
        'username'  => $this->username,
        'password'  => $this->password,
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
    );

    $data = json_encode($data);
    $sig = $this->generateSignature($data);
    $data = 'signed_body='. $sig . '.' . urlencode($data) . '&ig_sig_key_version=4';

    $login = $this->sendRequest('accounts/login/', true, $data, $this->agent, false);

    if ($login['status'] == 'fail')
    {
      throw new InstagramException($login['message']);
    }

    return $login;
  }

  public function uploadPhoto($photo, $caption = null)
  {
    $data = $this->getPostData($photo);
    $upload = $this->sendRequest('media/upload/', true, $data, $this->agent, true);

    $caption = preg_replace("/\r|\n/", "", $caption);

    $media_id = $upload['media_id'];
    $data = array(
      'device_id' => $this->device_id,
      'guid'      => $this->uuid,
      'media_id'  => $media_id,
      'device_timestamp' => time(),
      'source_type' => '5',
      'filter_type' => '0',
      'extra'       => '{}',
      'Content-Type'=> 'application/x-www-form-urlencoded; charset=UTF-8'
    );

    if (!is_null($caption))
      $data['caption'] = preg_replace("/\r|\n/", "", $caption);

    $data = json_encode($data);
    $sig = $this->generateSignature($data);
    $new_data = 'signed_body=' . $sig . '.' . urlencode($data) . '&ig_sig_key_version=4';

    // Now, configure the photo
    $conf = $this->sendRequest('media/configure/', true, $new_data, $this->agent, true);
  }

  protected function generateUserAgent()
  {
  	$resolutions = array('720x1280', '320x480', '480x800', '1024x768', '1280x720', '768x1024', '480x320');
  	$versions = array('GT-N7000', 'SM-N9000', 'GT-I9220', 'GT-I9100');
  	$dpis = array('120', '160', '320', '240');

  	$ver = $versions[array_rand($versions)];
  	$dpi = $dpis[array_rand($dpis)];
  	$res = $resolutions[array_rand($resolutions)];

  	return 'Instagram 4.'.mt_rand(1,2).'.'.mt_rand(0,2).' Android ('.mt_rand(10,11).'/'.mt_rand(1,3).'.'.mt_rand(3,5).'.'.mt_rand(0,5).'; '.$dpi.'; '.$res.'; samsung; '.$ver.'; '.$ver.'; smdkc210; en_US)';
  }

  protected function generateSignature($data)
  {
  	return hash_hmac('sha256', $data, 'b4a23f5e39b5929e0666ac5de94c89d1618a2916');
  }

  protected function generateUUID()
  {
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );
  }

  protected function getPostData($filename)
  {
  	if(!$filename)
    {
  		echo "The image doesn't exist " . $filename;
  	} else {
  		$post_data = array(
        'device_timestamp' => time(),
  			'photo' => '@'.$filename
      );
  		return $post_data;
  	}
  }

  protected function sendRequest($url, $post, $post_data, $user_agent, $cookies)
  {
    $ch = curl_init();
  	curl_setopt($ch, CURLOPT_URL, self::API_URL . $url);
  	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
  	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

  	if($post) {
  		curl_setopt($ch, CURLOPT_POST, true);
  		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
  	}

  	if($cookies) {
  		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
  	} else {
  		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
  	}

  	$response = curl_exec($ch);
  	$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  	curl_close($ch);

    if ($this->debug)
    {
      echo 'REQUEST: ' . $url . "\n";
      echo 'CODE: ' . $http . "\n";
      print_r($response);
      echo "\n\n";
    }

  	return json_decode($response, true);
  }

}
