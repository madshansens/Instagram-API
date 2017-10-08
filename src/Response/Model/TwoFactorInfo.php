<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class TwoFactorInfo extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'username'                    => '',
        'two_factor_identifier'       => '',
        'phone_verification_settings' => 'PhoneVerificationSettings',
        'obfuscated_phone_number'     => '',
    ];
}
