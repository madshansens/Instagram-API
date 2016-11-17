<?php

namespace InstagramAPI;

class SearchUserResponse extends Response
{
    var $has_more;
    var $num_results;
    /**
    * @var User[]
    */
    var $users;
}
