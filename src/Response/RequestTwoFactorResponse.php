<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class RequestTwoFactorResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'phone_verification_settings' => 'Model\PhoneVerificationSettings',
        'obfuscated_phone_number'     => '',
    ];
}
