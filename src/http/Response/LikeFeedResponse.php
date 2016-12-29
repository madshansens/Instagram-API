<?php

namespace InstagramAPI;

class LikeFeedResponse extends Response
{
    public $auto_load_more_enabled;
    /**
     * @var Item[]
     */
    public $items;
    public $more_available;
    public $patches;
    public $last_counted_at;
    public $num_results;
}
