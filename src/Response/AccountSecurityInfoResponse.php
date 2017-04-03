<?php

namespace InstagramAPI\Response;

class AccountSecurityInfoResponse extends \InstagramAPI\Response
{
    public $backup_codes;
    public $is_phone_confirmed;
    public $country_code;
    public $phone_number;
    public $is_two_factor_enabled;
    public $national_number;
}
