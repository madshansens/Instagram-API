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
            foreach($response['items'] as $item) {
                $items[] = new Item($item);
            }
            $this->items = $items;
            $this->more_available = $response['more_available'];
            $this->next_max_id = $response['next_max_id'];
            $this->total_count = $response['total_count'];
            $this->requires_review = $response['requires_review'];
            $this->new_photos = $response['new_photos'];
        } else {
            $this->setMessage($response['message']);
        }
        $this->setStatus($response['status']);
    }
}
