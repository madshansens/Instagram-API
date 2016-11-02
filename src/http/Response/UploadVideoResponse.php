<?php

namespace InstagramAPI;

class UploadVideoResponse extends Response
{
    protected $upload_id;
    protected $message = null;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->upload_id = $response['upload_id'];
            $this->setFullResponse($response);
            if (isset($response['message'])) {
                $this->setMessage($response['message']);
            }
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getUploadId()
    {
        return $this->upload_id;
    }
}
