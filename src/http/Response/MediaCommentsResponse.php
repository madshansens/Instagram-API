<?php

namespace InstagramAPI;

class MediaCommentsResponse extends Response
{
    protected $item = null;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            if ((isset($response['media'])) && (!empty($response['media']))) {
                $this->item = new Item($response['media']);
            }
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
