<?php

/**
 * Uploading a timeline album (aka carousel aka sidecar).
 */
require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

/////// MEDIA ////////
$media = [
    [
        'type'     => 'photo',
        'file'     => '', // Path to the photo file.
    ],
    [
        'type'     => 'photo',
        'file'     => '', // Path to the photo file.
        'usertags' => [ // Optional, lets you tag one or more users in a PHOTO.
            [
                'position' => [0.5, 0.5],
                'user_id'  => '123456789', // Must be a numerical UserPK ID.
            ],
        ],
    ],
    [
        'type'     => 'video',
        'file'     => '', // Path to the video file.
    ],
];
$captionText = ''; // Caption text to use for the album.
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->setUser($username, $password);
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->uploadTimelineAlbum($media, ['caption' => $captionText]);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
