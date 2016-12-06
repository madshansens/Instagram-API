<?php

namespace InstagramAPI;

class Inbox extends Response
{
    public $unseen_count;
    public $has_older;
    public $unseen_count_ts;
    /**
     * @var Thread[]
     */
    public $threads;
}
