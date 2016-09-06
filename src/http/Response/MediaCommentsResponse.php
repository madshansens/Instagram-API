<?php

namespace InstagramAPI;

class MediaCommentsResponse extends Response
{
    protected $item;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->item = new Item($response['media']);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getItem()
    {
        return $this->taken_at;
    }
}
