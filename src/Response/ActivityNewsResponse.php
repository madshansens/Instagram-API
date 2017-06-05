<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class ActivityNewsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\Story[]
     */
    public $new_stories;
    /**
     * @var Model\Story[]
     */
    public $old_stories;
    public $continuation;
    /**
     * @var Model\Story[]
     */
    public $friend_request_stories;
    /**
     * @var Model\Counts
     */
    public $counts;
    /**
     * @var Model\Subscription
     */
    public $subscription;
    public $continuation_token;
    public $ads_manager;
    /**
     * @var Model\Aymf
     */
    public $aymf;
}
