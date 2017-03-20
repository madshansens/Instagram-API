<?php

namespace InstagramAPI;

class AutoCompleteUserListResponse extends Response
{
    public $expires;
    /**
     * @var User[]
     */
    public $users;
}
