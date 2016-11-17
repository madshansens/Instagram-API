<?php

namespace InstagramAPI;

class ActivityNewsResponse extends Response
{
    var $new_stories;
    /**
    * @var Story[]
    */
    var $old_stories;
    var $continuation;
    var $friend_request_stories;
    var $counts;
    /**
    * @var mixed|null
    */
    var $subscription;
    var $continuation_token;
}
