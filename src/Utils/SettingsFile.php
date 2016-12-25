<?php

namespace InstagramAPI;

class SettingsFile
{
    public $cookiesPath; // public becouse used by HttpInterface
    private $sets;
    private $folderPath;

    public function isLogged()
    {
        if ((file_exists($this->cookiesPath)) && ($this->get('username_id') != null) && ($this->get('token') != null)) {
            return true;
        } else {
            return false;
        }
    }

    public function __construct($username, $path)
    {
        $this->cookiesPath = $path.$username.DIRECTORY_SEPARATOR.$username.'-cookies.dat';
        $this->settingsPath = $path.$username.DIRECTORY_SEPARATOR.'settings-'.$username.'.dat';

        $this->checkPermissions();

        $this->sets = [];
        if (file_exists($this->settingsPath)) {
            $fp = fopen($this->settingsPath, 'rb');
            while ($line = fgets($fp, 2048)) {
                $line = trim($line, ' ');
                if ($line[0] == '#') {
                    continue;
                }
                $kv = explode('=', $line, 2);
                $this->sets[$kv[0]] = trim($kv[1], "\r\n ");
            }
            fclose($fp);
        }
    }

    public function get($key, $default = null)
    {
        if ($key == 'sets') {
            return $this->sets;
        }

        if (isset($this->sets[$key])) {
            return $this->sets[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        if ($key == 'sets' or $key == 'path' or $key == 'username' or $key == 'folderPath') {
            return;
        }
        $this->sets[$key] = $value;
        $this->Save();
    }

    public function Save()
    {
        if (file_exists($this->settingsPath)) {
            unlink($this->settingsPath);
        }
        $fp = fopen($this->settingsPath, 'wb');
        fseek($fp, 0);
        foreach ($this->sets as $key => $value) {
            fwrite($fp, $key.'='.$value."\n");
        }
        fclose($fp);
    }

    protected function checkPermissions()
    {
        if (is_writable(dirname($this->settingsPath))) {
            return true;
        } elseif (mkdir(dirname($this->settingsPath), 0777, true)) {
            return true;
        } elseif (chmod(dirname($this->settingsPath), 0777)) {
            return true;
        }

        throw new InstagramException('The setting file is not writable');
    }
}
