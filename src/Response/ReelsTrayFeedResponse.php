<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class ReelsTrayFeedResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    /**
     * @var Model\Tray[]
     */
    public $tray;
    /**
     * @var Model\Broadcast[]
     */
    public $broadcasts;
    public $sticker_version;
    public $story_ranking_token;
}
