<?php

namespace InstagramAPI;

class ExploreResponse extends Response
{
    var $num_results;
    var $auto_load_more_enabled;
    
    var $items;
    var $more_available;
    var $next_max_id;
    var $max_id;

}
