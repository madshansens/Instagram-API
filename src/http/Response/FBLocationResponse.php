<?php

namespace InstagramAPI;

class FBLocationResponse extends Response
{
    protected $has_more;
    protected $items;

    public function __construct($response)
    {
        if (self::STATUS_OK == $response['status']) {
            $this->has_more = $response['has_more'];
            $items = [];
            foreach ($response['items'] as $item) {
                $this->items[] = new LocationItem($item);
            }
            $this->setFullResponse($response);
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }

    public function hasMore()
    {
        return $this->has_more;
    }

    public function getItems()
    {
        return $this->items;
    }
}
