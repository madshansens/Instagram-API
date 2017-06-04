<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class FriendshipStatus extends AutoPropertyHandler
{
    public $following;
    public $followed_by;
    public $incoming_request;
    public $outgoing_request;
    public $is_private;
    public $is_blocking_reel;
    public $is_muting_reel;
    public $blocking;
}
