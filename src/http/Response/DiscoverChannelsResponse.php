<?php

namespace InstagramAPI;

class DiscoverChannelsResponse extends Response
{
    public $auto_load_more_enabled;
    public $items;
    public $more_available;
    /**
    * @var string
    */
    public $next_max_id;
}
