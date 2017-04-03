<?php

namespace InstagramAPI\Response\Model;

class TwoFactorInfo extends \InstagramAPI\Response
{
    public $username;
    public $two_factor_identifier;
    /**
     * @var PhoneVerificationSettings
     */
    public $phone_verification_settings;
    public $obfuscated_phone_number;
}
