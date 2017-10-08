<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class FeedAysf extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'landing_site_type'  => '',
        'uuid'               => 'string',
        'view_all_text'      => '',
        'feed_position'      => '',
        'landing_site_title' => '',
        'is_dismissable'     => '',
        'suggestions'        => 'Suggestion[]',
        'should_refill'      => '',
        'display_new_unit'   => '',
        'fetch_user_details' => '',
        'title'              => '',
        'activator'          => '',
    ];
}
