<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class DirectShareInboxResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $shares;
    /**
     * @var string
     */
    public $max_id;
    public $new_shares;
    public $patches;
    public $last_counted_at;
    public $new_shares_info;
}
