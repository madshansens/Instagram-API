<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class DirectSeenItemPayload extends AutoPropertyHandler
{
    public $count;
    /** @var string */
    public $timestamp;
}
