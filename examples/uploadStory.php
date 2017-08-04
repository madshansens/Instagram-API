<?php

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

/////// MEDIA ////////
$photoFilename = '';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->setUser($username, $password);
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

// NOTE: This code will make the hashtag 'clickable', but you need to draw the hashtag,
//       or put an image overlay of the hashtag on top of your image.

$metadata =
    ['hashtags' => [
            // You can add more than one hashtag in the array.
            // All float values can be from 0.0 to 1.0.
            [
                'tag_name'         => '#test', // NOTE: This hashtag MUST appear in the caption.
                'x'                => 0.5, // x = 0.5 and y = 0.5 is center of screen.
                'y'                => 0.5,
                'width'            => 0.24305555,
                'height'           => 0.07347973,
                'rotation'         => 0.0,
                'is_sticker'       => false, // Don't change this value.
                'use_custom_title' => false, // Don't change this value.
            ],
            // ...
        ],
    'caption' => '#test this is a cool API', ];

try {
    $ig->story->uploadPhoto($photoFilename, $metadata);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
