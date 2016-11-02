<?php

namespace InstagramAPI;

class LocationFeedResponse extends Response
{
    protected $ranked_items = null;
    protected $media_count;
    protected $num_results;
    protected $auto_load_more_enabled;
    protected $items;
    protected $more_available;
    protected $next_max_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            if (array_key_exists('ranked_items', $response)) {
                foreach ($response['ranked_items'] as $ranked_item) {
                    $this->ranked_items[] = new Item($ranked_item);
                }
            }
            if (array_key_exists('items', $response)) {
                foreach ($response['items'] as $item) {
                    $this->items[] = new Item($item);
                }
            }
            $this->media_count = $response['media_count'];
            $this->num_results = $response['num_results'];
            $this->auto_load_more_enabled = $response['auto_load_more_enabled'];
            $this->more_available = $response['more_available'];
            $this->next_max_id = isset($response['next_max_id']) ? $response['next_max_id'] : null;
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getRankedItems()
    {
        return $this->ranked_items;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function getNumResults()
    {
        return $this->num_results;
    }

    public function autoLoadMoreEnabled()
    {
        return $this->auto_load_more_enabled;
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
