<?php

namespace InstagramAPI;

class TimelineFeedResponse extends Response
{
    public $num_results;
    public $is_direct_v2_enabled;
    public $auto_load_more_enabled;
    public $more_available;
    public $next_max_id;
      /*
     * @var _Message[]
     */
    public $_messages;
    /*
     * @var Item[]
     */
    public $feed_items;
    /*
     * @var FeedAysf|null
     */
    public $megaphone;
}
