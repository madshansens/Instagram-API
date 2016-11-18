<?php

namespace InstagramAPI;

class MediaInfoResponse extends Response
{
    public $auto_load_more_enabled;
    public $status;
    public $num_results;
    public $more_available;
    /**
     * @var Item[]
     */
    public $items;
}
