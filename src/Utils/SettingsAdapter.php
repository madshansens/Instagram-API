<?php

namespace InstagramAPI;

class SettingsAdapter
{
    public function __construct($config, $username)
    {
        if ($config['type'] == 'mysql') {
            $this->setting = new SettingsMysql($username, $config['username'], $config['password'], $config['host'], $config['database']);

            return;
        }
        if ($config['type'] == 'file') {
            $this->setting = new SettingsFile($username, $config['path']);

            return;
        }
        throw new Exception('Invalid settings adapter type.');
    }

    public function __call($func, $args)
    {
        // pass functions to releated settings class
        return call_user_func_array([$this->setting, $func], $args);
    }

    public function __get($prop)
    {
        return $this->setting->$prop;
    }
}
