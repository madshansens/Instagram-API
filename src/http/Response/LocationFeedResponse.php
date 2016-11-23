<?php

namespace InstagramAPI;

class LocationFeedResponse extends Response
{
    public $media_count;
    public $num_results;
    public $auto_load_more_enabled;
    /**
     * @var Item[]
     */
    public $items;
    /**
     * @var Item[]
     */
    public $ranked_items = null;
    public $more_available;
    public $next_max_id;
}
