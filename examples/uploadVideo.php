<?php

include __DIR__.'/../vendor/autoload.php';
require '../src/Instagram.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;

$videoFilename = '';  // path to the video
$caption = '';        // video caption
//////////////////////

$i = new \InstagramAPI\Instagram($debug, $truncatedDebug);

$i->setUser($username, $password);

try {
    $i->login();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

try {
    // Note that this performs a few automatic chunk upload retries by default,
    // in case of failing to upload the video chunks to Instagram's server!
    $i->uploadVideo($videoFilename, $caption);
    // or...
    // Example of using 8 retries instead of the default amount:
    // $i->uploadVideo($video, $caption, null, 8);
} catch (Exception $e) {
    echo $e->getMessage();
}
