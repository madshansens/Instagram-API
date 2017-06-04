<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

class TwoFactorInfo extends AutoPropertyHandler
{
    public $username;
    public $two_factor_identifier;
    /**
     * @var PhoneVerificationSettings
     */
    public $phone_verification_settings;
    public $obfuscated_phone_number;
}
