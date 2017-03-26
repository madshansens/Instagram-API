<?php

namespace InstagramAPI\Response;

class BroadcastLikeCountResponse extends \InstagramAPI\Response
{
    public $like_ts;
    public $likes;
    /**
     * @var User[]
     */
    public $likers;
}
