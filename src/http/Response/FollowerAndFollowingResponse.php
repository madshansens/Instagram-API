<?php

namespace InstagramAPI;

class FollowerAndFollowingResponse extends Response
{
    /**
     * @var User[]
     */
    public $users;
    public $next_max_id;
}
