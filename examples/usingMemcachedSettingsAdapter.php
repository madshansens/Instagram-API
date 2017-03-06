<?php

include __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

// Example using Memcached for storing settings and cookies
$i = new \InstagramAPI\Instagram($debug, $truncatedDebug, [
    'type'             => 'custom',
    'class'            => \InstagramAPI\SettingsAdapter\Memcached::class,
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

$i->setUser($username, $password);

try {
    $i->login();
} catch (Exception $e) {
    echo 'something went wrong '.$e->getMessage()."\n";
    exit(0);
}
