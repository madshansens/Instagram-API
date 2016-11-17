<?php

namespace InstagramAPI;

class LocationFeedResponse extends Response
{
    var $ranked_items = null;
    var $media_count;
    var $num_results;
    var $auto_load_more_enabled;
    var $items;
    var $more_available;
    var $next_max_id;
}
