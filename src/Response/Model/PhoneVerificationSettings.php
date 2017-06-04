<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class PhoneVerificationSettings extends AutoPropertyHandler
{
    public $resend_sms_delay_sec;
    public $max_sms_count;
    public $robocall_count_down_time_sec;
    public $robocall_after_max_sms;
}
