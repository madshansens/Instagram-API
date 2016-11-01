<?php

namespace InstagramAPI;

class UsertagsResponse extends Response
{
    protected $num_results;
    protected $auto_load_more_enabled;
    protected $items;
    protected $more_available;
    protected $next_max_id;
    protected $total_count;
    protected $requires_review;
    protected $new_photos;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->num_results = $response['num_results'];
            $this->auto_load_more_enabled = $response['auto_load_more_enabled'];
            $items = [];
            foreach ($response['items'] as $item) {
                $items[] = new Item($item);
            }
            $this->items = $items;
            $this->more_available = $response['more_available'];
            $this->next_max_id = isset($response['next_max_id']) ? $response['next_max_id'] : null;
            $this->total_count = $response['total_count'];
            $this->requires_review = $response['requires_review'];
            $this->new_photos = $response['new_photos'];
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    /**
     * @return mixed
     */
    public function getNumResults()
    {
        return $this->num_results;
    }

    /**
     * @return mixed
     */
    public function getAutoLoadMoreEnabled()
    {
        return $this->auto_load_more_enabled;
    }

    /**
     * @return mixed
     */
    public function getMoreAvailable()
    {
        return $this->more_available;
    }

    /**
     * @return mixed
     */
    public function getNextMaxId()
    {
        return $this->next_max_id;
    }

    /**
     * @return mixed
     */
    public function getTotalCount()
    {
        return $this->total_count;
    }

    /**
     * @return mixed
     */
    public function getRequiresReview()
    {
        return $this->requires_review;
    }

    /**
     * @return mixed
     */
    public function getNewPhotos()
    {
        return $this->new_photos;
    }

    /**
     * @return Item
     */
    public function getItems()
    {
        return $this->items;
    }
}
