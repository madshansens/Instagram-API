<?php

require __DIR__.'/../vendor/autoload.php';

/////// CONFIG ///////
$username = '';
$password = '';
$debug = true;
$truncatedDebug = false;
//////////////////////

/////// MEDIA ////////
$photoFilename = '';
$captionText = '';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

$ig->setUser($username, $password);
try {
    $ig->login();
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    $ig->uploadTimelinePhoto($photoFilename, $captionText);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
