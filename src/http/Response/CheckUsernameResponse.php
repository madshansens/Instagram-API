<?php

namespace InstagramAPI;

class CheckUsernameResponse extends Response
{
    public $username;
    public $available;
    public $status;
    public $error_type;
    public $error = false;

    public function __construct($response)
    {
        $this->username = $response['username'];
        $this->available = $response['available'];
        $this->status = $response['status'];
        if (isset($response['error_type'])) {
            $this->error_type = $response['error_type'];
        }
        if (isset($response['error'])) {
            $this->error = $response['error'];
        }
    }

    public function isAvailable()
    {
        if ($this->available == true) {
            return true;
        } else {
            return false;
        }
    }
}
