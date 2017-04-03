<?php

namespace InstagramAPI\Response\Model;

class PhoneVerificationSettings extends \InstagramAPI\Response
{
    public $resend_sms_delay_sec;
    public $max_sms_count;
    public $robocall_count_down_time_sec;
    public $robocall_after_max_sms;
}
