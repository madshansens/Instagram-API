<?php

namespace InstagramAPI;

class V2InboxResponse extends Response
{
    var $pending_requests_total;
    var $seq_id;
    var $pending_requests_users;
    /**
    * @var Inbox
    */
    var $inbox;
    var $subscription;

}
