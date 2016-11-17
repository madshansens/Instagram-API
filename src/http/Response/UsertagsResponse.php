<?php

namespace InstagramAPI;

class UsertagsResponse extends Response
{
    public $num_results;
    public $auto_load_more_enabled;
    public $items;
    public $more_available;
    public $next_max_id;
    public $total_count;
    public $requires_review;
    public $new_photos;
}
