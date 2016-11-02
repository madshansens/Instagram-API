<?php

namespace InstagramAPI;

class ExploreResponse extends Response
{
    protected $num_results;
    protected $auto_load_more_enabled;
    protected $items;
    protected $more_available;
    protected $next_max_id;
    protected $max_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->num_results = $response['num_results'];
            $this->auto_load_more_enabled = $response['auto_load_more_enabled'];
            $this->more_available = $response['more_available'];
            $this->next_max_id = isset($response['next_max_id']) ? $response['next_max_id'] : null;
            $this->max_id = $response['max_id'];
            $items = [];
            foreach ($response['items'] as $item) {
                if (isset($item['media'])) {
                    $items[] = new Item($item['media']);
                }
            }
            $this->items = $items;
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function getExpires()
    {
        return $this->expires;
    }

    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }
}
