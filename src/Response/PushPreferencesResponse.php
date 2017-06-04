<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class PushPreferencesResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\PushSettings[]
     */
    public $push_settings;
    public $likes;
    public $comments;
    public $comment_likes;
    public $like_and_comment_on_photo_user_tagged;
    public $live_broadcast;
    public $new_follower;
    public $follow_request_accepted;
    public $contact_joined;
    public $pending_direct_share;
    public $direct_share_activity;
    public $user_tagged;
    public $notification_reminders;
    public $first_post;
    public $announcements;
    public $ads;
    public $view_count;
    public $report_updated;
}
