<?php

namespace InstagramAPI;

class ExploreResponse extends Response
{
    public $num_results;
    public $auto_load_more_enabled;

    public $items;
    public $more_available;
    public $next_max_id;
    public $max_id;
}
