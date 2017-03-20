<?php

namespace InstagramAPI;

class SuggestedUsersResponse extends Response
{
    /**
     * @var User[]
     */
    public $users;
    public $is_backup;
}
