<?php

namespace InstagramAPI;

class SearchUserResponse extends Response
{
    public $has_more;
    public $num_results;
    /*
    * @var User[]
    */
    public $users;
}
