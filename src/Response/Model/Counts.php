<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class Counts extends AutoPropertyHandler
{
    public $relationships;
    public $requests;
    public $photos_of_you;
    public $usertags;
    public $comments;
    public $likes;
    public $comment_likes;
    public $campaign_notification;
}
