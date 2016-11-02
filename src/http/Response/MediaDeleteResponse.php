<?php

namespace InstagramAPI;

class MediaDeleteResponse extends Response
{
    protected $did_delete;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->did_delete = $response['did_delete'];
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function didDelete()
    {
        return $this->did_delete;
    }
}
