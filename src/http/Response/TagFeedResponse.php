<?php

namespace InstagramAPI;

class TagFeedResponse extends Response
{
    protected $num_results;
    protected $ranked_items;
    protected $auto_load_more_enabled;
    protected $items;
    protected $more_available;
    protected $next_max_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->num_results = $response['num_results'];
            $rankedItems       = [];
            foreach ($response['ranked_items'] as $rankItem) {
                $rankedItems[] = new Item($rankItem);
            }
            $this->ranked_items           = $rankedItems;
            $this->auto_load_more_enabled = $response['auto_load_more_enabled'];
            $items                        = [];
            foreach ($response['items'] as $item) {
                $items[] = new Item($item);
            }
            $this->items          = $items;
            $this->more_available = $response['more_available'];
            $this->next_max_id    = $response['next_max_id'];
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getNumResults()
    {
        return $this->num_results;
    }

    /**
     * @return Item[]
     */
    public function getRankedItems()
    {
        return $this->ranked_items;
    }

    public function getAutoLoadMoreEnabled()
    {
        return $this->auto_load_more_enabled;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    public function moreAvailable()
    {
        return $this->more_available;
    }

    public function getNextMaxId()
    {
        return $this->next_max_id;
    }
}
