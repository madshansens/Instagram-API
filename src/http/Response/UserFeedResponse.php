<?php

namespace InstagramAPI;

class UserFeedResponse extends Response
{
    var $num_results;
    var $auto_load_more_enabled;
    /**
     * @var Item[]
     */
    var $items;
    var $more_available;
    var $next_max_id = null;

}
