<?php

namespace InstagramAPI\Response;

class RequestTwoFactorResponse extends \InstagramAPI\Response
{
    /**
     * @var Model\PhoneVerificationSettings
     */
    public $phone_verification_settings;
    public $obfuscated_phone_number;
}
