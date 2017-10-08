<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Injected extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'label'                        => '',
        'show_icon'                    => '',
        'hide_label'                   => '',
        'invalidation'                 => '',
        'is_demo'                      => '',
        'view_tags'                    => '',
        'is_holdout'                   => '',
        'tracking_token'               => '',
        'show_ad_choices'              => '',
        'ad_title'                     => '',
        'about_ad_params'              => '',
        'direct_share'                 => '',
        'ad_id'                        => 'string',
        'display_viewability_eligible' => '',
        'hide_reasons_v2'              => '',
        'hide_flow_type'               => '',
        'cookies'                      => '',
        'lead_gen_form_id'             => 'string',
    ];
}
