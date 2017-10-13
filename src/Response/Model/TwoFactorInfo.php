<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * TwoFactorInfo.
 *
 * @method mixed getObfuscatedPhoneNumber()
 * @method PhoneVerificationSettings getPhoneVerificationSettings()
 * @method mixed getTwoFactorIdentifier()
 * @method mixed getUsername()
 * @method bool isObfuscatedPhoneNumber()
 * @method bool isPhoneVerificationSettings()
 * @method bool isTwoFactorIdentifier()
 * @method bool isUsername()
 * @method $this setObfuscatedPhoneNumber(mixed $value)
 * @method $this setPhoneVerificationSettings(PhoneVerificationSettings $value)
 * @method $this setTwoFactorIdentifier(mixed $value)
 * @method $this setUsername(mixed $value)
 * @method $this unsetObfuscatedPhoneNumber()
 * @method $this unsetPhoneVerificationSettings()
 * @method $this unsetTwoFactorIdentifier()
 * @method $this unsetUsername()
 */
class TwoFactorInfo extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'username'                    => '',
        'two_factor_identifier'       => '',
        'phone_verification_settings' => 'PhoneVerificationSettings',
        'obfuscated_phone_number'     => '',
    ];
}
