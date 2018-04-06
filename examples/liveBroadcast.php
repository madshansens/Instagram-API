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
$videoFilename = '';
//////////////////////

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

try {
    // NOTE: This code will create a broadcast, which will give us an RTMP url
    // where we are supposed to stream-upload the media we want to broadcast.
    //
    // The following code is using FFMPEG to broadcast, although other
    // alternatives are valid too, like OBS (Open Broadcaster Software,
    // https://obsproject.com).
    //
    // For more information on FFMPEG, see:
    // https://github.com/mgp25/Instagram-API/issues/1488#issuecomment-324271177
    // and for OBS, see:
    // https://github.com/mgp25/Instagram-API/issues/1488#issuecomment-333365636

    // Get FFmpeg handler and ensure that the application exists on this system.
    // NOTE: You can supply custom path to the ffmpeg binary, or just leave NULL
    // to autodetect it.
    $ffmpegPath = null;
    $ffmpeg = \InstagramAPI\Media\Video\FFmpeg::factory($ffmpegPath);

    // Tell Instagram that we want to perform a livestream.
    $stream = $ig->live->create();
    $broadcastId = $stream->getBroadcastId();
    $ig->live->start($broadcastId);

    // Switch from RTMPS to RTMP upload URL, since RTMPS doesn't work well.
    $streamUploadUrl = preg_replace(
        '#^rtmps://([^/]+?):443/#ui',
        'rtmp://\1:80/',
        $stream->getUploadUrl()
    );

    // Broadcast the entire video file.
    // NOTE: The video is broadcasted asynchronously.
    $broadcastProcess = $ffmpeg->runAsync(sprintf(
        '-rtbufsize 256M -re -i %s -acodec libmp3lame -ar 44100 -b:a 128k -pix_fmt yuv420p -profile:v baseline -s 720x1280 -bufsize 6000k -vb 400k -maxrate 1500k -deinterlace -vcodec libx264 -preset veryfast -g 30 -r 30 -f flv %s',
        escapeshellarg($videoFilename),
        escapeshellarg($streamUploadUrl)
    ));

    // The following while loop performs different requests to obtain live information of the broadcast.
    // NOTE: This is required if you want the comments and the likes to appear in the post-live feed.
    // NOTE: These requests are sent while the video is being broadcasted.
    $lastCommentTs = 0;
    $lastLikeTS = 0;
    while ($broadcastProcess->isRunning()) {
        // Get broadcast comments.
        // The latest comment timestamp is required for the next getComments() request.
        // There are two types of comments: System comments and user comments.
        // We compare both and keep the latest timestamp.
        $commentsData = $ig->live->getComments($broadcastId, $lastCommentTs);

        $systemComments = $commentsData->getSystemComments();
        $comments = $commentsData->getComments();
        if ($systemComments) {
            $lastCommentTS = end($systemComments)->getCreatedAt();
        }
        if ($comments) {
            $lastCommentTs = end($comments)->getCreatedAt() > $lastCommentTs ? end($comments)->getCreatedAt() : $lastCommentTs;
        }
        // Get broadcast heartbeat and viewer count.
        $ig->live->getHeartbeatAndViewerCount($broadcastId);
        // Get broadcast like count.
        // The latest like timestamp is required for the next getLikeCount() request.
        $likes = $ig->live->getLikeCount($broadcastId, $lastLikeTS);
        $lastLikeTS = $likes->getLikeTs();
        sleep(2);
    }

    // Get the final viewer list of the broadcast.
    // NOTE: You should only use this after the broadcast has ended.
    $ig->live->getFinalViewerList($broadcastId);

    // End the broadcast stream.
    // NOTE: Instagram will ALSO end the stream if your broadcasting software
    // itself sends a RTMP signal to end the stream. FFmpeg doesn't do that
    // (without patching), but OBS sends such a packet. So be aware of that.
    $ig->live->end($stream->getBroadcastId());

    // Once the broadcast has ended, you can optionally add the finished
    // broadcast to your post-live feed (saved replay).
    $ig->live->addToPostLive($stream->getBroadcastId());
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
}
