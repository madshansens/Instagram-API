<?php

namespace InstagramAPI\Response;

class SendItemResponse extends \InstagramAPI\Response
{
    public $action;
    public $status_code;
    /** @var Model\SendItemPayload */
    public $payload;
}
