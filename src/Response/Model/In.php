<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class In extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'position'                   => 'Position',
        'user'                       => 'User',
        'time_in_video'              => '',
        'start_time_in_video_in_sec' => '',
        'duration_in_video_in_sec'   => '',
    ];
}
