<?php

namespace InstagramAPI;

class Reel extends Response
{
    public $id;
    /**
     * @var Item[]
     */
    public $items;

    public $user;
    public $expiring_at;
    public $seen;
    public $can_reply;
}
