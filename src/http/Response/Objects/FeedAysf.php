<?php

namespace InstagramAPI;

class FeedAysf
{
    protected $landing_site_type;
    protected $uuid;
    protected $view_all_text;
    protected $feed_position;
    protected $landing_site_title;
    protected $is_dismissable;
    protected $suggestions;
    protected $should_refill;
    protected $display_new_unit;
    protected $fetch_user_details;
    protected $title;

    public function __construct($data)
    {
        $this->landing_site_type = $data['landing_site_type'];
        $this->uuid = $data['uuid'];
        $this->view_all_text = $data['view_all_text'];
        $this->feed_position = $data['feed_position'];
        $this->landing_site_title = $data['landing_site_title'];
        $this->is_dismissable = $data['is_dismissable'];
        $suggestions = [];
        if ((isset($data['suggestions'])) && (!empty($data['suggestions']))) {
            foreach ($data['suggestions'] as $suggestion) {
                $suggestions[] = new Suggestion($suggestion);
            }
        }
        $this->suggestions = $suggestions;
        $this->should_refill = $data['should_refill'];
        $this->display_new_unit = $data['display_new_unit'];
        $this->fetch_user_details = $data['fetch_user_details'];
        $this->title = $data['title'];
    }

    public function getLandingSiteType()
    {
        return $this->landing_site_type;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getViewAllText()
    {
        return $this->view_all_text;
    }

    public function getFeedPosition()
    {
        return $this->feed_position;
    }

    public function getLandingSiteTitle()
    {
        return $this->landing_site_title;
    }

    public function isDismissable()
    {
        return $this->is_dismissable;
    }

    /**
     * @return Suggestion[]
     */
    public function getSuggestions()
    {
        return $this->suggestions;
    }

    public function shouldRefill()
    {
        return $this->should_refill;
    }

    public function displayNewUnit()
    {
        return $this->display_new_unit;
    }

    public function fetchUserDetails()
    {
        return $this->fetch_user_details;
    }

    public function getTitle()
    {
        return $this->title;
    }
}
