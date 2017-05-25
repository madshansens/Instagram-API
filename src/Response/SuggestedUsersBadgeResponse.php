<?php

namespace InstagramAPI\Response;

class SuggestedUsersBadgeResponse extends \InstagramAPI\Response
{
    public $should_badge;
    /**
     * @var string[]
     */
    public $new_suggestion_ids;
}
