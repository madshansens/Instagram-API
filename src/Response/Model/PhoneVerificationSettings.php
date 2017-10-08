<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class PhoneVerificationSettings extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'resend_sms_delay_sec'         => '',
        'max_sms_count'                => '',
        'robocall_count_down_time_sec' => '',
        'robocall_after_max_sms'       => '',
    ];
}
