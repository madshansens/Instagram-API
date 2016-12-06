<?php

namespace InstagramAPI;

class RankedRecipientsResponse extends Response
{
    public $expires;
    /**
     * @var RankedRecipientsUserList[]
     */
    public $ranked_recipients;
    public $filtered;
}

class RankedRecipientsUserList extends Response
{
    /**
     * @var User
     */
    public $user;
}
