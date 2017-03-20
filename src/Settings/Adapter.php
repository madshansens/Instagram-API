<?php

namespace InstagramAPI\Settings;

class Adapter
{
    public $storage;

    protected function getCmdOptions(
        array $longOpts)
    {
        $cmdOptions = getopt('', $longOpts);

        if (!is_array($cmdOptions)) {
            $cmdOptions = [];
        }

        return $cmdOptions;
    }

    protected function getUserConfig(
        $settingName,
        array $config,
        array $cmdOptions)
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

    /**
     * @param array  $config
     * @param string $username
     *
     * @throws \InstagramAPI\Exception\SettingsException
     */
    public function __construct(
        $config,
        $username)
    {
        switch ($config['type']) {
        case 'mysql':
            $cmdOptions = $this->getCmdOptions([
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

            $this->storage = new \InstagramAPI\Settings\Storage\MySQL($username, $mysqlOptions);
            break;
        case 'file':
            $cmdOptions = $this->getCmdOptions([
                'settings_path::',
            ]);

            // Settings that can optionally be provided.
            $settingsPath = $this->getUserConfig('settings_path', $config, $cmdOptions);

            $this->storage = new \InstagramAPI\Settings\Storage\File($username, $settingsPath);
            break;
        case 'custom':
            if (!isset($config['class']) || !class_exists($config['class']) || !in_array(StorageInterface::class, class_implements($config['class']))) {
                throw new \InstagramAPI\Exception\SettingsException('Unknown custom settings class specified.');
            }

            $customClass = $config['class'];

            /** @var \InstagramAPI\Settings\StorageInterface $settings */
            $settings = new $customClass($username, $config);

            $this->storage = $settings;
            break;
        default:
            throw new \InstagramAPI\Exception\SettingsException('Unrecognized settings type.');
        }
    }

    public function __call(
        $func,
        $args)
    {
        // pass functions to releated settings class
        return call_user_func_array([$this->storage, $func], $args);
    }

    public function __get(
        $prop)
    {
        return $this->storage->$prop;
    }
}
