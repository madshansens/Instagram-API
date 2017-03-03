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
                'db_tablename::',
            ];
            $options = getopt('', $longOpts);

            if (!$options) {
                $options = [];
            }

            $env_username = getenv('DB_USERNAME');
            $env_password = getenv('DB_PASSWORD');
            $env_host = getenv('DB_HOST');
            $env_name = getenv('DB_NAME');
            $env_tablename = getenv('DB_TABLENAME');

            $dbUsername = array_key_exists('username', $config) ? $config['username'] : array_key_exists('db_username', $options) ? $options['db_username'] : $env_username !== false ? $env_username : null;
            $dbPassword = array_key_exists('password', $config) ? $config['password'] : array_key_exists('db_password', $options) ? $options['db_password'] : $env_password !== false ? $env_password : null;
            $dbHost = array_key_exists('host', $config) ? $config['host'] : array_key_exists('db_host', $options) ? $options['db_host'] : $env_host !== false ? $env_host : null;
            $dbName = array_key_exists('name', $config) ? $config['name'] : array_key_exists('db_name', $options) ? $options['db_name'] : $env_name !== false ? $env_name : null;
            $dbTableName = array_key_exists('tablename', $config) ? $config['tablename'] : array_key_exists('db_tablename', $options) ? $options['db_tablename'] : $env_tablename !== false ? $env_tablename : null;

            $mysqlOptions = [
                'instagramUsername' => $username,
                'dbName'            => $dbName,
                'dbTableName'       => $dbTableName,
            ];
            if (isset($config['pdo'])) {
                // If the 'pdo' config property is set, assume the user wants
                // to re-use an existing PDO connection. In that case we ignore
                // the dbUsername/dbPassword/dbHost parameters and use the PDO.
                $mysqlOptions['pdo'] = $config['pdo'];
            } else {
                $mysqlOptions['dbUsername'] = $dbUsername;
                $mysqlOptions['dbPassword'] = $dbPassword;
                $mysqlOptions['dbHost'] = $dbHost;
            }

            $this->setting = new SettingsMysql($mysqlOptions);
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
        case 'custom':
            if (!isset($config['class']) || !class_exists($config['class']) || !in_array(SettingsAdapter\SettingsInterface::class, class_implements($config['class']))) {
                throw new InstagramException('Unknown custom settings class specified', ErrorCode::INTERNAL_SETTINGS_ERROR);
            }

            $customClass = $config['class'];
            /** @var SettingsAdapter\SettingsInterface $settings */
            $settings = new $customClass($username, $config);

            $this->setting = $settings;
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
