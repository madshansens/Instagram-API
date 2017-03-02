<?php

namespace InstagramAPI;

class PopularFeedResponse extends Response
{
    /**
    * @var string
    */
    public $next_max_id;
    public $more_available;
    public $auto_load_more_enabled;
    /**
     * @var Item[]
     */
    public $items;
    public $num_results;
    /**
    * @var string
    */
    public $max_id;
}
