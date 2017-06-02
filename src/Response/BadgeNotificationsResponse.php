<?php

namespace InstagramAPI\Response;

class BadgeNotificationsResponse extends \InstagramAPI\Response
{
    public $badge_payload; // Only exists if you have notifications, contains data keyed by userId.
}
