<?php

namespace InstagramAPI;

class ChallengeResponse extends Response
{
    public $status;

    public function __construct($response)
    {
        $this->status = $response['status'];
    }
}
