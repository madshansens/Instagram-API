<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class BusinessNode extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        '__typename'                         => '',
        'followers_count'                    => '',
        'followers_delta_from_last_week'     => '',
        'posts_count'                        => '',
        'posts_delta_from_last_week'         => '',
        'last_week_impressions'              => '',
        'week_over_week_impressions'         => '',
        'last_week_reach'                    => '',
        'week_over_week_reach'               => '',
        'last_week_profile_visits'           => '',
        'week_over_week_profile_visits'      => '',
        'last_week_website_visits'           => '',
        'week_over_week_website_visits'      => '',
        'last_week_call'                     => '',
        'week_over_week_call'                => '',
        'last_week_text'                     => '',
        'week_over_week_text'                => '',
        'last_week_email'                    => '',
        'week_over_week_email'               => '',
        'last_week_get_direction'            => '',
        'week_over_week_get_direction'       => '',
        'average_engagement_count'           => '',
        'last_week_impressions_day_graph'    => '',
        'last_week_reach_day_graph'          => '',
        'last_week_profile_visits_day_graph' => '',
        '_summary_poststYGwD'                => '',
        'state'                              => '',
        '_summary_storiesjmsA2'              => '',
        'followers_unit_state'               => '',
        '_today_hourly_graph2Iuh8n'          => '',
        'gender_graph'                       => '',
        'all_followers_age_graph'            => '',
        'followers_top_cities_graph'         => '',
        '_summary_promotions2ubm1F'          => '',
    ];
}
