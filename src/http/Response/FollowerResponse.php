<?php

namespace InstagramAPI;

class FollowerResponse extends Response
{
    /**
     * @var User[]
     */
    public $users;
    public $next_max_id;
}
