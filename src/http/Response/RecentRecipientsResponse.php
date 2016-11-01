<?php

namespace InstagramAPI;

class RecentRecipientsResponse extends Response
{
    protected $expiration_interval;
    protected $recent_recipients;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->expiration_interval = $response['expiration_interval'];
            $this->recent_recipients = $response['recent_recipients'];
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getExpirationInterval()
    {
        return $this->expiration_interval;
    }

    public function getRecentRecipients()
    {
        return $this->recent_recipients;
    }
}
