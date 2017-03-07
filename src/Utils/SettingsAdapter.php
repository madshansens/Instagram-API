<?php

namespace InstagramAPI;

class SettingsAdapter
{
    protected function getCmdOptions(array $longOpts)
    {
        $cmdOptions = getopt(null, $longOpts);

        if (!is_array($cmdOptions)) {
            $cmdOptions = [];
        }

        return $cmdOptions;
    }

    protected function getUserConfig($settingName, array $config, array $cmdOptions)
    {
        // Look for a user-provided value for the setting.
        if (array_key_exists($settingName, $config)) {
            // Constructor config array has highest precedence.
            return $config[$settingName];
        } elseif (array_key_exists($settingName, $cmdOptions)) {
            // Command line options have second highest precedence.
            return $cmdOptions[$settingName];
        } else {
            // Environment variables have third highest precedence.
            // All settings must be UPPERCASED when in environment.
            $envValue = getenv(strtoupper($settingName));
            if ($envValue !== false) {
                return $envValue;
            }
        }

        // Couldn't find any user-provided value. Automatically returns null.
        // NOTE: Damn you StyleCI for not allowing "return null;" for clarity.
    }

    public function __construct($config, $username)
    {
        switch ($config['type']) {
        case 'mysql':
            $cmdOptions = getCmdOptions([
                'db_username::',
                'db_password::',
                'db_host::',
                'db_name::',
                'db_tablename::',
            ]);

            // Settings that can be used regardless of connection method.
            $mysqlOptions = [
                'db_tablename'       => $this->getUserConfig('db_tablename', $config, $cmdOptions),
            ];

            if (isset($config['pdo'])) {
                // If the 'pdo' is set in constructor configg, assume user wants
                // to re-use an existing PDO connection. In that case we ignore
                // the username/password/host/name parameters and use the PDO.
                $mysqlOptions['pdo'] = $config['pdo'];
            } else {
                // Settings that can be provided if a PDO object is not used.
                $mysqlOptions['db_username'] = $this->getUserConfig('db_username', $config, $cmdOptions);
                $mysqlOptions['db_password'] = $this->getUserConfig('db_password', $config, $cmdOptions);
                $mysqlOptions['db_host'] = $this->getUserConfig('db_host', $config, $cmdOptions);
                $mysqlOptions['db_name'] = $this->getUserConfig('db_name', $config, $cmdOptions);
            }

            $this->setting = new SettingsMysql($username, $mysqlOptions);
            break;
        case 'file':
            $cmdOptions = getCmdOptions([
                'settings_path::',
            ]);

            // Settings that can optionally be provided.
            $settingsPath = $this->getUserConfig('settings_path', $config, $cmdOptions);

            $this->setting = new SettingsFile($username, $settingsPath);
            break;
        case 'custom':
            if (!isset($config['class']) || !class_exists($config['class']) || !in_array(SettingsAdapter\SettingsInterface::class, class_implements($config['class']))) {
                throw new InstagramException('Unknown custom settings class specified.', ErrorCode::INTERNAL_SETTINGS_ERROR);
            }

            $customClass = $config['class'];
            /** @var SettingsAdapter\SettingsInterface $settings */
            $settings = new $customClass($username, $config);

            $this->setting = $settings;
            break;
        default:
            throw new InstagramException('Unrecognized settings type.', ErrorCode::INTERNAL_SETTINGS_ERROR);
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
