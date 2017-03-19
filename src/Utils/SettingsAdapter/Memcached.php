<?php

namespace InstagramAPI\SettingsAdapter;

/**
 * Class Memcached.
 *
 * @author ilyk <ilyk@ilyk.im>
 */
class Memcached implements SettingsInterface
{
    /**
     * @var \Memcached
     */
    protected $memcached;

    /**
     * Memcached constructor.
     *
     * @param string $instagramUsername
     * @param array  $config
     */
    public function __construct($instagramUsername, $config)
    {
        $this->memcached = $memcached = new \Memcached((isset($config['persistent_id']) ? $config['persistent_id'] : 'instagram'));

        if (isset($config['init_callback']) && is_callable($config['init_callback'])) {
            $config['init_callback']($memcached);
        }

        if (isset($config['memcache_options'])) {
            $memcached->setOptions((array) $config['memcache_options']);
        }

        if (isset($config['servers'])) {
            $memcached->addServers($config['servers']);
        } elseif (isset($config['server'])) {
            $memcached->addServer(
                $config['server']['host'],
                (isset($config['server']['port']) ? $config['server']['port'] : 11211),
                (isset($config['server']['weight']) ? $config['server']['weight'] : 0)
            );
        } else {
            $memcached->addServer('localhost', 11211);
        }
    }

    /**
     * @param string $key
     * @param mixed  $value
     *
     * @return void
     */
    public function set($key, $value)
    {
        $this->memcached->set($key, $value);
    }

    /**
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $result = $this->memcached->get($key);

        return \Memcached::RES_NOTFOUND === ($this->memcached->getResultCode() ? $default : $result);
    }

    /**
     * Does a preliminary guess about whether we're logged in.
     *
     * The session it looks for may be expired, so there's no guarantee.
     *
     * @return bool
     */
    public function maybeLoggedIn()
    {
        return $this->get('id') !== null // Cannot use empty() since row can be 0.
            && !empty($this->get('username_id'))
            && !empty($this->get('token'));
    }

    /**
     * @return void
     */
    public function Save()
    {
    }
}
