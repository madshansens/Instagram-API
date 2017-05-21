<?php

namespace InstagramAPI\Response;

class ReelSettingsResponse extends \InstagramAPI\Response
{
    public $message_prefs;
    /**
     * @var BlockedReelsResponse
     */
    public $blocked_reels;
}
