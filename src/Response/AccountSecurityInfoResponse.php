<?php

namespace InstagramAPI\Response;

use InstagramAPI\AutoPropertyHandler;
use InstagramAPI\ResponseInterface;
use InstagramAPI\ResponseTrait;

class AccountSecurityInfoResponse extends AutoPropertyHandler implements ResponseInterface
{
    use ResponseTrait;

    public $backup_codes;
    public $is_phone_confirmed;
    public $country_code;
    public $phone_number;
    public $is_two_factor_enabled;
    public $national_number;
}
