<?php

namespace InstagramAPI;

class autoCompleteUserListResponse extends Response
{
    public $expires;
    /**
     * @var User[]
     */
    public $users;
}
