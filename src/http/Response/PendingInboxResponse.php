<?php

namespace InstagramAPI;

class PendingInboxResponse extends Response
{
    public $seq_id;
    public $pending_requests_total;
    public $inbox;
}
