<?php

namespace InstagramAPI\Response;

class BlockedListResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\User[]
     */
    public $blocked_list;
    public $page_size;
}
