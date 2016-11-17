<?php

namespace InstagramAPI;

class TagFeedResponse extends Response
{
    public $num_results;
    public $ranked_items = null;
    public $auto_load_more_enabled;
    public $items;
    public $more_available;
    public $next_max_id;
}
