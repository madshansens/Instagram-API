<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method Model\HiddenEntities getRecent()
 * @method bool isRecent()
 * @method setRecent(Model\HiddenEntities $value)
 */
class FacebookHiddenEntitiesResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\HiddenEntities
     */
    public $recent;
}
