<?php

namespace InstagramAPI\Response;

class DirectSendItemResponse extends \InstagramAPI\Response
{
    public $action;
    public $status_code;
    /** @var Model\DirectSendItemPayload */
    public $payload;
}
