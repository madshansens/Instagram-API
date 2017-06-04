<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class BroadcastOwner extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $pk;
    /**
     * @var FriendshipStatus
     */
    public $friendship_status;
    public $full_name;
    public $is_verified;
    public $profile_pic_url;
    /**
     * @var string
     */
    public $profile_pic_id;
    public $is_private;
    public $username;
}
