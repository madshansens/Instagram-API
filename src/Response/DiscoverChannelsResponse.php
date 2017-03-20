<?php

namespace InstagramAPI\Response;

class DiscoverChannelsResponse extends \InstagramAPI\Response
{
    public $auto_load_more_enabled;
    public $items;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
}
