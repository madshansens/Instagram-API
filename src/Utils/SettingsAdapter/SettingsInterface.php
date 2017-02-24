<?php

namespace InstagramAPI\SettingsAdapter;

/**
 * Interface SettingsInterface.
 *
 * @author ilyk <ilyk@ilyk.im>
 */
interface SettingsInterface
{
    /**
     * SettingsInterface constructor.
     *
     * @param string $instagramUsername
     * @param array  $config
     */
    public function __construct($instagramUsername, $config);

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set($key, $value);

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * @return bool
     */
    public function isLogged();

    /**
     * @return void
     */
    public function Save();
}
