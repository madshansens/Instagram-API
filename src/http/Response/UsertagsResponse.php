<?php

namespace InstagramAPI;

class UsertagsResponse extends Response
{
    var $num_results;
    var $auto_load_more_enabled;
    var $items;
    var $more_available;
    var $next_max_id;
    var $total_count;
    var $requires_review;
    var $new_photos;
}
