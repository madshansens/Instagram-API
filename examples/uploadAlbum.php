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

/**
 * Uploading a timeline album (aka carousel aka sidecar).
 */

/////// MEDIA ////////
$media = [ // Albums can contain between 2 and 10 photos/videos.
    [
        'type'     => 'photo',
        'file'     => '', // Path to the photo file.
    ],
    [
        'type'     => 'photo',
        'file'     => '', // Path to the photo file.
        /* TAGS COMMENTED OUT UNTIL YOU READ THE BELOW:
        'usertags' => [ // Optional, lets you tag one or more users in a PHOTO.
            [
                'position' => [0.5, 0.5],

                // WARNING: THE USER ID MUST BE VALID. INSTAGRAM WILL VERIFY IT
                // AND IF IT'S WRONG THEY WILL SAY "media configure error".
                'user_id'  => '123456789', // Must be a numerical UserPK ID.
            ],
        ],
        */
    ],
    [
        'type'     => 'video',
        'file'     => '', // Path to the video file.
    ],
];
$captionText = ''; // Caption to use for the album.
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

////// NORMALIZE MEDIA //////
// All album files must have the same aspect ratio.
// We copy the app's behavior by using the first file
// as template for all subsequent ones.
$mediaOptions = [
    'targetFeed' => \InstagramAPI\Constants::FEED_TIMELINE_ALBUM,
    // Uncomment to expand media instead of cropping it.
    //'operation' => \InstagramAPI\Media\InstagramMedia::EXPAND,
];
foreach ($media as &$item) {
    /** @var \InstagramAPI\Media\InstagramMedia|null $validMedia */
    $validMedia = null;
    switch ($item['type']) {
        case 'photo':
            $validMedia = new \InstagramAPI\Media\Photo\InstagramPhoto($item['file'], $mediaOptions);
            break;
        case 'video':
            $validMedia = new \InstagramAPI\Media\Video\InstagramVideo($item['file'], $mediaOptions);
            break;
        default:
            // Ignore unknown media type.
    }
    if ($validMedia === null) {
        continue;
    }

    try {
        $item['file'] = $validMedia->getFile();
        // We must prevent the InstagramMedia object from destructing too early,
        // because the media class auto-deletes the processed file during their
        // destructor's cleanup (so we wouldn't be able to upload those files).
        $item['__media'] = $validMedia; // Save object in an unused array key.
    } catch (\Exception $e) {
        continue;
    }
    if (!isset($mediaOptions['minAspectRatio'])) {
        /** @var \InstagramAPI\Media\MediaDetails $mediaDetails */
        $mediaDetails = $validMedia instanceof \InstagramAPI\Media\Photo\InstagramPhoto
            ? new \InstagramAPI\Media\Photo\PhotoDetails($item['file'])
            : new \InstagramAPI\Media\Video\VideoDetails($item['file']);
        $aspectRatio = $mediaDetails->getAspectRatio();
        $mediaOptions['minAspectRatio'] = $aspectRatio;
        $mediaOptions['maxAspectRatio'] = $aspectRatio;
        // When we tell the media processor to resize all media to an exact
        // aspect ratio (by setting BOTH min and max to the same value above),
        // we're actually asking for something that is almost always IMPOSSIBLE
        // to achieve unless ALL of the input media files were the EXACT same
        // original input size. That's because pixels cannot be subdivided into
        // smaller fractions than "1" (and therefore, hitting super-exact ratios
        // is usually impossible). So in MOST cases, when we ask for something
        // like "1.25385" ratio (ie. the ratio from the first file), other files
        // may at-best become a TINY fraction of a percent off, such as "1.2543"
        // instead. Therefore we MUST enable "allowNewAspectDeviation", which
        // tells the media processor to aim at the CLOSEST possible aspect ratio
        // but will allow itself to be sliiiightly off from the target when it's
        // impossible to hit the exact ratio. The album will still look perfect.
        // WARNING: If you DON'T enable this feature, the media processor will
        // throw exceptions on every impossible media file!
        $mediaOptions['allowNewAspectDeviation'] = true;
    }
}
unset($item);
/////////////////////////////

try {
    $ig->timeline->uploadAlbum($media, ['caption' => $captionText]);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
