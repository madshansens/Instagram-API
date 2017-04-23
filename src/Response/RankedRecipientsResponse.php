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
    /**
     * @var string
     */
    public $request_id;
}
