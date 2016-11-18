<?php

namespace InstagramAPI;

class UserFeedResponse extends Response
{
    public $num_results;
    public $auto_load_more_enabled;
    /**
     * @var Item[]
     */
    public $items;
    public $more_available;
    public $next_max_id = null;
}
