<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class DirectInboxResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

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
