<?php

namespace InstagramAPI\Response;

class DirectSeenItemResponse extends \InstagramAPI\Response
{
    public $action;
    /** @var Model\DirectSeenItemPayload */
    public $payload; // this is the number of unseen items
}
