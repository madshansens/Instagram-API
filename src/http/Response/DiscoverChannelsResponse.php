<?php

namespace InstagramAPI;

class DiscoverChannelsResponse extends Response
{
    protected $auto_load_more_enabled;
    protected $items;
    protected $more_available;
    protected $next_max_id;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->auto_load_more_enabled = $response['auto_load_more_enabled'];
            $this->more_available = $response['more_available'];
            $this->next_max_id = isset($response['next_max_id']) ? $response['next_max_id'] : null;
            $items = [];
            $row_items = [];
            foreach ($response['items'] as $key => $item) {
                if (!isset($response['items'][$key]['title'])) {
                    foreach ($item['row_items'] as $keyi => $row_item) {
                        $row_items[$keyi] = new RowItem($row_item);
                    }
                    $this->items[$key] = $row_items;
                } else {
                    $this->items[$key] = $response['items'][$key]['title'];
                }
            }
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
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

    public function autoLoadMoreEnabled()
    {
        return $this->auto_load_more_enabled;
    }
}
