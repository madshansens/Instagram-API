<?php

namespace InstagramAPI;

class SearchUserResponse extends Response
{
    public $has_more;
    public $num_results;
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var User[]
     */
    public $users;
}
