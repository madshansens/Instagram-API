<?php

namespace InstagramAPI;

class FollowerAndFollowingResponse extends Response
{
    /**
     * @var User[]
     */
    public $users;
    /**
     * @var string
     */
    public $next_max_id;
    public $page_size;
    public $big_list;
}
