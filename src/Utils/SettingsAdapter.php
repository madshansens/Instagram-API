<?php

namespace InstagramAPI;

class SettingsAdapter
{
    public function __construct($config, $username)
    {
        switch ($config['type']) {
        case 'mysql':
            $longOpts = [
                'db_username::',
                'db_password::',
                'db_host::',
                'db_name::',
            ];
            $options = getopt('', $longOpts);
                
            if (!$options) {
                $options = [];
            }
                
            $env_username = getenv('DB_USERNAME');
            $env_password = getenv('DB_PASSWORD');
            $env_host = getenv('DB_HOST');
            $env_name = getenv('DB_NAME');

            $dbUsername = array_key_exists('username', $config) ? $config['username'] : array_key_exists('db_username', $options) ? options['db_username'] : $env_username !== false ? $env_username : null;
            $password = array_key_exists('password', $config) ? $config['password'] : array_key_exists('db_password', $options) ? options['db_password'] : $env_password !== false ? $env_password : null;
            $host = array_key_exists('host', $config) ? $config['host'] : array_key_exists('db_host', $options) ? options['db_host'] : $env_host !== false ? $env_host : null;
            $name = array_key_exists('name', $config) ? $config['name'] : array_key_exists('db_name', $options) ? options['db_name'] : $env_name !== false ? $env_name : null;

            $this->setting = new SettingsMysql($username, $dbUsername, $password, $host, $name);
            break;
        case 'file':
            $longOpts = [
                'settings_path::',
            ];
            $options = getopt('', $longOpts);
                
            if (!$options) {
                $options = [];
            }
                
            $env_settings_path = getenv('SETTINGS_PATH');
            if (array_key_exists('path', $config)) {
                $settings_path = $config['path'];
            } elseif (array_key_exists('settings_path', $options)) {
                $settings_path = $options['settings_path'];
            } elseif ($env_settings_path !== false) {
                $settings_path = $env_settings_path;
            } else {
                $settings_path = null;
            }
            $this->setting = new SettingsFile($username, $settings_path);
            break;
        default:
            throw new InstagramException('Unrecognized settings type', ErrorCode::INTERNAL_SETTINGS_ERROR);
        }
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
