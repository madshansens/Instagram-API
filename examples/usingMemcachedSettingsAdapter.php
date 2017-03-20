<?php

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

// Example of using Memcached for storing settings and cookies.
$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug, [
    'type'             => 'custom',
    'class'            => \InstagramAPI\Settings\Storage\Memcached::class,
    'persistent_id'    => 'instagram',
    'memcache_options' => [
        Memcached::OPT_PREFIX_KEY => 'settings_',
    ],
    'servers' => [[
        'host'   => 'localhost',
        'port'   => 11211,
        'weight' => 0,
    ], [
        'host'   => 'other.host.com',
        'port'   => 11211,
        'weight' => 1,
    ]],
]);

try {
    $ig->setUser($username, $password);
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}
