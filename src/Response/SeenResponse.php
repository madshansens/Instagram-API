<?php

namespace InstagramAPI\Response;

class SeenResponse extends \InstagramAPI\Response
{
    public $action;
    /** @var Model\UnseenCount */
    public $payload;
}
