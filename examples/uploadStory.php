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
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

// You don't have to provide hashtags or locations for your story. It is
// optional! But we will show you how to do both...

// NOTE: This code will make the hashtag area 'clickable', but YOU need to
// manually draw the hashtag or a sticker-image on top of your image yourself
// before uploading, if you want the tag to actually be visible on-screen!

// NOTE: The same thing happens when a location sticker is added. And the
// "location_sticker" WILL ONLY work if you also add the "location" as shown
// below.

// If we want to attach a location, we must find a valid Location object first:
try {
    $location = $ig->location->search('40.7439862', '-73.998511')->getVenues()[0];
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}

// Now create the metadata array:
$metadata = [
    // (optional) Captions can always be used, like this:
    'caption'  => '#test This is a great API!',

    // (optional) To add a hashtag, do this:
    'hashtags' => [
        // Note that you can add more than one hashtag in this array.
        [
            'tag_name'         => 'test', // Hashtag WITHOUT the '#'! NOTE: This hashtag MUST appear in the caption.
            'x'                => 0.5, // Range: 0.0 - 1.0. Note that x = 0.5 and y = 0.5 is center of screen.
            'y'                => 0.5, // Also note that X/Y is setting the position of the CENTER of the clickable area.
            'width'            => 0.24305555, // Clickable area size, as percentage of image size: 0.0 - 1.0
            'height'           => 0.07347973, // ...
            'rotation'         => 0.0,
            'is_sticker'       => false, // Don't change this value.
            'use_custom_title' => false, // Don't change this value.
        ],
        // ...
    ],

    // (optional) To add a location, do BOTH of these:
    'location_sticker' => [
        'width'         => 0.89333333333333331,
        'height'        => 0.071281859070464776,
        'x'             => 0.5,
        'y'             => 0.2,
        'rotation'      => 0.0,
        'is_sticker'    => true,
        'location_id'   => $location->getExternalId(),
    ],
    'location' => $location,
];

try {
    // This example will upload the image via our automatic media resizer class.
    // It will ensure that the story file matches the ~9:16 (portrait) aspect
    // ratio needed by Instagram stories. You have nothing to worry about, since
    // the class uses temporary files if the input needs processing, and it
    // never overwrites your original file. There are many other options for
    // the MediaAutoResizer class, so you should read its class documentation!
    $resizer = new \InstagramAPI\MediaAutoResizer($photoFilename, ['targetFeed' => \InstagramAPI\Constants::FEED_STORY]);
    $ig->story->uploadPhoto($resizer->getFile(), $metadata);

    // NOTE: Providing metadata for story uploads is OPTIONAL. If you just want
    // to upload it without any tags/location, simply do the following instead:
    // $ig->story->uploadPhoto($resizer->getFile());
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
