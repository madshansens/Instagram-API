<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method Model\Suggested[] getRecent()
 * @method bool isRecent()
 * @method setRecent(Model\Suggested[] $value)
 */
class RecentFacebookSearchesResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\Suggested[]
     */
    public $recent;
}
