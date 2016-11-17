<?php

namespace InstagramAPI;

class CheckUsernameResponse extends Response
{
    var $username;
    var $available;
    var $status;
    var $error = false;
}
