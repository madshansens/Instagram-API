<?php

namespace InstagramAPI\Response;

class CheckEmailResponse extends \InstagramAPI\Response
{
    public $username;
    public $confirmed;
    public $status;
    public $valid;
    public $username_suggestions;

    public function __construct($response)
    {
        $this->valid = $response['valid'];
    }

    public function isAvailable()
    {
        if ($this->valid == true) {
            return true;
        } else {
            return false;
        }
    }
}
