<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class DirectRankedRecipientsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

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
