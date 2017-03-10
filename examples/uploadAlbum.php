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
$truncatedDebug = false;

$media = [
    [
        'type'     => 'photo',
        'file'     => '', // path to the photo file
    ],
    [
        'type'     => 'photo',
        'file'     => '', // path to the photo file
        'usertags' => [ // optional, lets you tag one or more users in the photo
            [
                'position' => [0.5, 0.5],
                'user_id'  => 0,
            ],
        ],
    ],
    [
        'type'     => 'video',
        'file'     => '', // path to the video file
    ],
];

$caption = '';

//////////////////////

$i = new \InstagramAPI\Instagram($debug, $truncatedDebug);

$i->setUser($username, $password);

try {
    $i->login();
} catch (Exception $e) {
    $e->getMessage();
    exit();
}

try {
    $i->uploadTimelineAlbum($media, $caption);
} catch (Exception $e) {
    echo $e->getMessage();
}
