<?php

namespace InstagramAPI\Response;

class DirectPendingInboxResponse extends \InstagramAPI\Response
{
    /**
     * @var string
     */
    public $seq_id;
    public $pending_requests_total;
    /**
     * @var Model\DirectInbox
     */
    public $inbox;
}
