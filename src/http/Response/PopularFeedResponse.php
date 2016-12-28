<?php

namespace InstagramAPI;

class PopularFeedResponse extends Response
{
    public $next_max_id;
    public $more_available;
    public $auto_load_more_enabled;
    /**
     * @var Item[]
     */
    public $items;
    public $num_results;
    public $max_id;
}
