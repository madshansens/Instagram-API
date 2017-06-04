<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class SuggestedUsers extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $id;
    public $view_all_text;
    public $title;
    public $auto_dvance;
    public $type;
    public $tracking_token;
    public $landing_site_type;
    public $landing_site_title;
    public $upsell_fb_pos;
    /*
     * @var Suggestion[]
     */
    public $suggestions;
}
