<?php

namespace InstagramAPI;

class ChallengeSMS
{
    protected $username;
    protected $settingsPath;
    protected $settings;
    protected $userAgent;
    protected $debug;

    protected $step = 0;
    // 0 = not ready
    // 1 = put phone
    // 2 = put sec number
    // 3 = Done!
    protected $token = null;

    public function __construct($username, $settingsPath = null, $debug = false)
    {
        $this->username = $username;
        $this->debug = $debug;
        if (is_null($settingsPath)) {
            $this->settingsPath = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.$username.DIRECTORY_SEPARATOR;
            if (!file_exists($this->settingsPath)) {
                mkdir($this->settingsPath, 0777, true);
            }
        }
        $this->settings = new Settings($this->settingsPath.'settings-'.$username.'.dat');
        $this->userAgent = 'Instagram 9.6.0 Android (21/5.0.1; 300dpi; 768x1190; LGE/google; Nexus 4; mako; mako; en_US)';
    }

    public function getStep()
    {
        return $this->step;
    }

    public function startChallenge()
    {
        $this->trigger();
    }

    public function setPhone($value)
    {
        $this->trigger(['phone_number' => $value]);
    }

    public function setCode($value)
    {
        $this->trigger(['response_code' => $value]);
    }

    public function reset()
    {
        $this->trigger([], 'reset/');
    }

    public function trigger($POST = null, $url = '')
    {
        $headers = null;
        if (!is_null($this->token) && !is_null($POST)) {
            $POST['csrfmiddlewaretoken'] = $this->token;
            $headers = [
                'Connection: close',
                'Accept: */*',
                'X-Requested-With: com.instagram.android',
                'Referer: https://i.instagram.com/challenge/',
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Accept-Language: en-US',
                'Upgrade-Insecure-Requests: 1',
            ];
        }

        $response = $this->request('https://i.instagram.com/challenge/'.$url, $headers, $POST);

        if (is_null($this->token)) {
            preg_match('#Set-Cookie: csrftoken=([^;]+)#', $response[0], $token);
            $this->token = $token[1];
        }

        if ($response[2] == 302 || preg_match('/^Location: https/m', $response[0]) > 0) {
            $this->step = 3;
        } elseif (preg_match('/id="id_phone_number"/', $response[1]) > 0) {
            $this->step = 1;
        } elseif (preg_match('/>Security code</', $response[1]) > 0) {
            $this->step = 2;
        } else {
            $this->step = 0;
        }

        echo 'Step: '.$this->step."\n";
    }

    public function request($endpoint, $headers = null, $post = null, $first = true)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        if (!is_null($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        //curl_setopt($ch, CURLOPT_VERBOSE, $this->debug);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->settingsPath.$this->username.'-cookies.dat');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->settingsPath.$this->username.'-cookies.dat');

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, count($post));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }

        $resp = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($resp, 0, $header_len);
        $body = substr($resp, $header_len);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($this->debug) {
            echo "REQUEST: $httpCode $endpoint \n";
            if (!is_null($post)) {
                echo 'DATA: '.http_build_query($post)."\n";
            }
            echo "RESPONSE: $body\n\n";
        }

        return [$header, $body, $httpCode];
    }
}
