<?php

namespace InstagramAPI;

class DirectShareInboxResponse extends Response
{
    public $shares;
    public $max_id;
    public $new_shares;
    public $patches;
    public $last_counted_at;
    public $new_shares_info;
}
