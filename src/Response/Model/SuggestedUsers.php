<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class SuggestedUsers extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'                 => 'string',
        'view_all_text'      => '',
        'title'              => '',
        'auto_dvance'        => '',
        'type'               => '',
        'tracking_token'     => '',
        'landing_site_type'  => '',
        'landing_site_title' => '',
        'upsell_fb_pos'      => '',
        'suggestions'        => 'Suggestion[]',
        'netego_type'        => '',
    ];
}
