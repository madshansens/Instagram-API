<?php

namespace InstagramAPI;

class LocationFeedResponse extends Response
{
    public $ranked_items = null;
    public $media_count;
    public $num_results;
    public $auto_load_more_enabled;
    public $items;
    public $more_available;
    public $next_max_id;
}
