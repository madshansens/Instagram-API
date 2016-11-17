<?php

namespace InstagramAPI;

class CheckUsernameResponse extends Response
{
    public $username;
    public $available;
    public $status;
    public $error = false;
}
