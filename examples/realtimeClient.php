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

$ig = new \InstagramAPI\Instagram($debug, $truncatedDebug);

try {
    $ig->login($username, $password);
} catch (\Exception $e) {
    echo 'Something went wrong: '.$e->getMessage()."\n";
    exit(0);
}

$loop = \React\EventLoop\Factory::create();
if ($debug) {
    $logger = new \Monolog\Logger('rtc');
    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Logger::INFO));
} else {
    $logger = null;
}
$rtc = new \InstagramAPI\Realtime($ig, $loop, $logger);
$rtc->on('live-started', function (\InstagramAPI\Realtime\Event\Payload\Live $live) {
    printf('[RTC] Live broadcast %s has been started%s', $live->broadcast_id, PHP_EOL);
});
$rtc->on('live-stopped', function (\InstagramAPI\Realtime\Event\Payload\Live $live) {
    printf('[RTC] Live broadcast %s has been stopped%s', $live->broadcast_id, PHP_EOL);
});
$rtc->on('direct-story-created', function (\InstagramAPI\Response\Model\DirectThread $thread) {
    printf('[RTC] Story %s has been created%s', $thread->thread_id, PHP_EOL);
});
$rtc->on('direct-story-updated', function ($threadId, $threadItemId, \InstagramAPI\Response\Model\DirectThreadItem $threadItem) {
    printf('[RTC] Item %s has been created in story %s%s', $threadItemId, $threadId, PHP_EOL);
});
$rtc->on('direct-story-screenshot', function ($threadId, \InstagramAPI\Realtime\Event\Payload\Screenshot $screenshot) {
    printf('[RTC] %s has taken screenshot of story %s%s', $screenshot->action_user_dict->username, $threadId, PHP_EOL);
});
$rtc->on('direct-story-action', function ($threadId, \InstagramAPI\Response\Model\ActionBadge $storyAction) {
    printf('[RTC] Story has badge %s%s', $threadId, $storyAction->action_type, PHP_EOL);
});
$rtc->on('thread-created', function ($threadId, \InstagramAPI\Response\Model\DirectThread $thread) {
    printf('[RTC] Thread %s has been created%s', $threadId, PHP_EOL);
});
$rtc->on('thread-updated', function ($threadId, \InstagramAPI\Response\Model\DirectThread $thread) {
    printf('[RTC] Thread %s has been updated%s', $threadId, PHP_EOL);
});
$rtc->on('thread-notify', function ($threadId, $threadItemId, \InstagramAPI\Realtime\Event\Payload\Notify $notify) {
    printf('[RTC] Thread %s has notification from %s%s', $threadId, $notify->user_id, PHP_EOL);
});
$rtc->on('thread-seen', function ($threadId, $userId, \InstagramAPI\Response\Model\DirectThreadLastSeenAt $seenAt) {
    printf('[RTC] Thread %s has been checked by %s%s', $threadId, $userId, PHP_EOL);
});
$rtc->on('thread-activity', function ($threadId, \InstagramAPI\Realtime\Event\Payload\Activity $activity) {
    printf('[RTC] Thread %s has some activity made by %s%s', $threadId, $activity->sender_id, PHP_EOL);
});
$rtc->on('thread-item-created', function ($threadId, $threadItemId, \InstagramAPI\Response\Model\DirectThreadItem $threadItem) {
    printf('[RTC] Item %s has been created in thread %s%s', $threadItemId, $threadId, PHP_EOL);
});
$rtc->on('thread-item-updated', function ($threadId, $threadItemId, \InstagramAPI\Response\Model\DirectThreadItem $threadItem) {
    printf('[RTC] Item %s has been updated in thread %s%s', $threadItemId, $threadId, PHP_EOL);
});
$rtc->on('thread-item-removed', function ($threadId, $threadItemId) {
    printf('[RTC] Item %s has been removed from thread %s%s', $threadItemId, $threadId, PHP_EOL);
});
$rtc->on('client-context-ack', function (\InstagramAPI\Realtime\Action\Ack $ack) {
    printf('[RTC] Received ACK for %s with status %s%s', $ack->payload->client_context, $ack->status, PHP_EOL);
});
$rtc->on('unseen-count-update', function (\InstagramAPI\Response\Model\DirectSeenItemPayload $payload) {
    printf('[RTC] Updating unseen count to %d%s', $payload->count, PHP_EOL);
});
$rtc->on('error', function (\Exception $e) use ($rtc, $loop) {
    printf('[!!!] Got fatal error from Realtime: %s%s', $e->getMessage(), PHP_EOL);
    $rtc->stop();
    $loop->stop();
});
$rtc->start();

$loop->run();
