<?php

namespace InstagramAPI\Response;

class RankedRecipientsResponse extends \InstagramAPI\Response
{
    public $expires;
    /**
     * @var Model\RankedRecipient[]
     */
    public $ranked_recipients;
    public $filtered;
    public $request_id;
}
