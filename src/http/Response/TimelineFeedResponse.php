<?php

namespace InstagramAPI;

class TimelineFeedResponse extends Response
{
    var $num_results;
    var $is_direct_v2_enabled;
    var $auto_load_more_enabled;
    var $more_available;
    var $next_max_id;
      /**
     * @var _Message[]
     */
    var $_messages;
    /**
     * @var Item[]
     */
    var $feed_items;
    /**
     * @var FeedAysf|null
     */
    var $megaphone;

}
