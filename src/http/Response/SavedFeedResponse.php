<?php

namespace InstagramAPI;

class SavedFeedResponse extends Response
{
    /**
     * @var SavedFeedItem[]
     */
    public $items;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
    public $auto_load_more_enabled;
    public $num_results;
}
