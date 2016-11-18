<?php

namespace InstagramAPI;

class ActivityNewsResponse extends Response
{
    public $new_stories;
    /**
     * @var Story[]
     */
    public $old_stories;
    public $continuation;
    public $friend_request_stories;
    public $counts;
    /**
     * @var mixed|null
     */
    public $subscription;
    public $continuation_token;
}
