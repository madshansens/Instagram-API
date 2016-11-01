<?php

namespace InstagramAPI;

class PendingInboxResponse extends Response
{
    protected $seq_id;
    protected $pending_requests_total;
    protected $inbox;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->seq_id = $response['seq_id'];
            $this->pending_requests_total = $response['pending_requests_total'];
            $this->inbox = new Inbox($response['inbox']);
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getSeqId()
    {
        return $this->seq_id;
    }

    public function getPendingRequestsTotal()
    {
        return $this->pending_requests_total;
    }

    /**
     * @return Inbox
     */
    public function getInbox()
    {
        return $this->inbox;
    }
}
