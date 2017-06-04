<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class TopLive extends AutoPropertyHandler
{
    /**
     * @var BroadcastOwner[]
     */
    public $broadcast_owners;
}
