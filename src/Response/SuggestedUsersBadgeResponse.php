<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class SuggestedUsersBadgeResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $should_badge;
    /**
     * @var string[]
     */
    public $new_suggestion_ids;
}
