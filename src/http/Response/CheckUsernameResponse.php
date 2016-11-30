<?php

namespace InstagramAPI;

class CheckUsernameResponse extends Response
{
    public $username;
    public $available;
    public $status;
    public $error_type;
    public $error = false;

    public function isAvailable()
    {
        var_dump($this->available);
        if ($this->available == true) {
            return true;
        } else {
            return false;
        }
    }
}
