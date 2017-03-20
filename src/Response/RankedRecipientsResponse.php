<?php

namespace InstagramAPI\Response;

class RankedRecipientsResponse extends \InstagramAPI\Response
{
    public $expires;
    /**
     * @var Model\RankedRecipientsUserList[]
     */
    public $ranked_recipients;
    public $filtered;
}

class RankedRecipientsUserList extends \InstagramAPI\Response
{
    /**
     * @var User
     */
    public $user;
}
