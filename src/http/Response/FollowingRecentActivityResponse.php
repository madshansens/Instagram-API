<?php

namespace InstagramAPI;

class FollowingRecentActivityResponse extends Response
{
    /**
     * @var Story[]
     */
    public $stories;
    /**
     * @var string
     */
    public $next_max_id;
    public $auto_load_more_enabled;
    public $megaphone;
}
