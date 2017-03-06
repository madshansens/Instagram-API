<?php

/**
 * Upload Album example (aka carousel aka sidecar).
 */
include __DIR__.'/../vendor/autoload.php';
require '../src/Instagram.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;

$photos = [
    [
        'file'     => '',
        'usertags' => [
            [
                'position' => [0.5, 0.5],
                'user_id'  => 0,
            ],
        ],
    ], [
        'file'     => '',
        'usertags' => [
            [
                'position' => [0.5, 0.5],
                'user_id'  => 0,
            ],
        ],
    ],
];

$caption = '';

//////////////////////

$i = new \InstagramAPI\Instagram($debug);

$i->setUser($username, $password);

try {
    $i->login();
} catch (Exception $e) {
    $e->getMessage();
    exit();
}

try {
    $i->uploadPhotoAlbum($photos, $caption);
} catch (Exception $e) {
    echo $e->getMessage();
}
