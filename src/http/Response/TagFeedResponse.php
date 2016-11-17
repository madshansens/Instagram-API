<?php

namespace InstagramAPI;

class TagFeedResponse extends Response
{
    var $num_results;
    var $ranked_items = null;
    var $auto_load_more_enabled;
    var $items;
    var $more_available;
    var $next_max_id;
}
