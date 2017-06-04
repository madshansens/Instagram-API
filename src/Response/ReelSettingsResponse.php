<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class ReelSettingsResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $message_prefs;
    /**
     * @var Model\BlockedReels
     */
    public $blocked_reels;
}
