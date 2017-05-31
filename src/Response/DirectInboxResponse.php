<?php

namespace InstagramAPI\Response;

class DirectInboxResponse extends \InstagramAPI\Response
{
    public $pending_requests_total;
    /**
     * @var string
     */
    public $seq_id;
    /**
     * @var Model\User[]
     */
    public $pending_requests_users;
    /**
     * @var Model\DirectInbox
     */
    public $inbox;
    /**
     * @var Model\Subscription
     */
    public $subscription;
}
