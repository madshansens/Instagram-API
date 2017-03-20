<?php

namespace InstagramAPI\Settings;

/**
 * Interface StorageInterface.
 *
 * @author ilyk <ilyk@ilyk.im>
 */
interface StorageInterface
{
    /**
     * Constructor.
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
     * Does a preliminary guess about whether we're logged in.
     *
     * The session it looks for may be expired, so there's no guarantee.
     *
     * @return bool
     */
    public function maybeLoggedIn();

    /**
     * @return void
     */
    public function save();
}
