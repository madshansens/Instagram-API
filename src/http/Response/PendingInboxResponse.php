<?php

namespace InstagramAPI;

class PendingInboxResponse extends Response
{
    /**
     * @var string
     */
    public $seq_id;
    public $pending_requests_total;
    /**
     * @var Inbox
     */
    public $inbox;
}
