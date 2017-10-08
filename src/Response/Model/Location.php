<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Location extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'name'                 => '',
        'external_id_source'   => 'string',
        'external_source'      => '',
        'address'              => '',
        'lat'                  => 'float',
        'lng'                  => 'float',
        'external_id'          => 'string',
        'facebook_places_id'   => 'string',
        'city'                 => '',
        'pk'                   => 'string',
        'short_name'           => '',
        'facebook_events_id'   => 'string',
        'start_time'           => '',
        'end_time'             => '',
        'location_dict'        => 'Location',
        'type'                 => '',
        'profile_pic_url'      => '',
        'profile_pic_username' => '',
        'time_granularity'     => '',
        'timezone'             => '',
    ];
}
