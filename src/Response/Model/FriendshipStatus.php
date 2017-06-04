<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class FriendshipStatus extends AutoPropertyHandler
{
    // NOTE: We must use full paths to all model objects in THIS class, because
    // "FriendshipsShowResponse" re-uses this object and JSONMapper won't be
    // able to find these sub-objects if the paths aren't absolute!

    public $following;
    public $followed_by;
    public $incoming_request;
    public $outgoing_request;
    public $is_private;
    public $is_blocking_reel;
    public $is_muting_reel;
    public $blocking;
}
