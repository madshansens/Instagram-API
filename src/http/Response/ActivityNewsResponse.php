<?php

namespace InstagramAPI;

class ActivityNewsResponse extends Response
{
    /**
     * @var Story[]
     */
    public $new_stories;
    /**
     * @var Story[]
     */
    public $old_stories;
    public $continuation;
    /**
     * @var Story[]
     */
    public $friend_request_stories;
    /**
     * @var Counts
     */
    public $counts;
    /**
     * @var Subscription
     */
    public $subscription;
    public $continuation_token;
}
