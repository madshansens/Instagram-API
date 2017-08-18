<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

/**
 * @method mixed getAutoLoadMoreEnabled()
 * @method Model\FeedItem[] getFeedItems()
 * @method mixed getIsDirectV2Enabled()
 * @method Model\FeedAysf getMegaphone()
 * @method mixed getMoreAvailable()
 * @method string getNextMaxId()
 * @method mixed getNumResults()
 * @method bool isAutoLoadMoreEnabled()
 * @method bool isFeedItems()
 * @method bool isIsDirectV2Enabled()
 * @method bool isMegaphone()
 * @method bool isMoreAvailable()
 * @method bool isNextMaxId()
 * @method bool isNumResults()
 * @method setAutoLoadMoreEnabled(mixed $value)
 * @method setFeedItems(Model\FeedItem[] $value)
 * @method setIsDirectV2Enabled(mixed $value)
 * @method setMegaphone(Model\FeedAysf $value)
 * @method setMoreAvailable(mixed $value)
 * @method setNextMaxId(string $value)
 * @method setNumResults(mixed $value)
 */
class TimelineFeedResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $num_results;
    public $is_direct_v2_enabled;
    public $auto_load_more_enabled;
    public $more_available;
    /**
     * @var string
     */
    public $next_max_id;
    /**
     * @var Model\FeedItem[]
     */
    public $feed_items;
    /**
     * @var Model\FeedAysf
     */
    public $megaphone;
}
