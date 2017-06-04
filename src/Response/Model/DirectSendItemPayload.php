<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class DirectSendItemPayload extends AutoPropertyHandler
{
    public $client_context;
    public $message;
    /** @var string */
    public $item_id;
    /** @var string */
    public $timestamp;
    /** @var string */
    public $thread_id;
}
