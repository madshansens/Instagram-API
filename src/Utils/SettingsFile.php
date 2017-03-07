<?php

namespace InstagramAPI;

class SettingsFile
{
    private $sets;
    public $cookiesPath; // Public because it's used by HttpInterface.
    private $settingsPath;

    public function __construct($username, $settingsPath)
    {
        // Decide which settings-file paths to use.
        if (empty($settingsPath)) {
            $settingsPath = Constants::DATA_DIR;
        }
        $this->cookiesPath = $settingsPath.$username.DIRECTORY_SEPARATOR.$username.'-cookies.dat';
        $this->settingsPath = $settingsPath.$username.DIRECTORY_SEPARATOR.$username.'-settings.dat';

        // Test write-permissions to the settings file and create if necessary.
        $this->checkPermissions();

        // Read all existing settings.
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

    public function isLogged()
    {
        if ((file_exists($this->cookiesPath)) && ($this->get('username_id') !== null) && ($this->get('token') !== null)) {
            return true;
        } else {
            return false;
        }
    }

    public function get($key, $default = null)
    {
        if ($key == 'sets') {
            return $this->sets; // Return 'sets' itself which contains all data.
        }

        if (isset($this->sets[$key])) {
            return $this->sets[$key];
        }

        return $default;
    }

    public function set($key, $value)
    {
        if ($key == 'sets') {
            return; // Don't allow writing to special 'sets' key.
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

    private function checkPermissions()
    {
        if (is_writable(dirname($this->settingsPath))) {
            return true;
        } elseif (mkdir(dirname($this->settingsPath), 0777, true)) {
            return true;
        } elseif (chmod(dirname($this->settingsPath), 0777)) {
            return true;
        }

        throw new InstagramException('The settings file is not writable.', ErrorCode::INTERNAL_SETTINGS_ERROR);
    }
}
