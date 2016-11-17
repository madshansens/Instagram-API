<?php

namespace InstagramAPI;

class Response extends AutoResponseFunctionSetter
{
    const STATUS_OK = 'ok';
    const STATUS_FAIL = 'fail';

    var $status;
    var $message;
    var $fullResponse;

    public function __construct()
    {
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setFullResponse($response)
    {
        $this->fullResponse = $response;
    }

    public function getFullResponse()
    {
        return $this->fullResponse;
    }

    public function isOk()
    {
        return $this->getStatus() == self::STATUS_OK;
    }
}
