<?php

namespace InstagramAPI\Response;

class DirectRankedRecipientsResponse extends \InstagramAPI\Response
{
    public $expires;
    /**
     * @var Model\DirectRankedRecipient[]
     */
    public $ranked_recipients;
    public $filtered;
    /**
     * @var string
     */
    public $request_id;
    public $rank_token;
}
