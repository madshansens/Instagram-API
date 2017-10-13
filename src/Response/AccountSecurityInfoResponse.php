<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

/**
 * AccountSecurityInfoResponse.
 *
 * @method mixed getBackupCodes()
 * @method mixed getCountryCode()
 * @method mixed getIsPhoneConfirmed()
 * @method mixed getIsTwoFactorEnabled()
 * @method mixed getMessage()
 * @method mixed getNationalNumber()
 * @method mixed getPhoneNumber()
 * @method string getStatus()
 * @method Model\_Message[] get_Messages()
 * @method bool isBackupCodes()
 * @method bool isCountryCode()
 * @method bool isIsPhoneConfirmed()
 * @method bool isIsTwoFactorEnabled()
 * @method bool isMessage()
 * @method bool isNationalNumber()
 * @method bool isPhoneNumber()
 * @method bool isStatus()
 * @method bool is_Messages()
 * @method $this setBackupCodes(mixed $value)
 * @method $this setCountryCode(mixed $value)
 * @method $this setIsPhoneConfirmed(mixed $value)
 * @method $this setIsTwoFactorEnabled(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setNationalNumber(mixed $value)
 * @method $this setPhoneNumber(mixed $value)
 * @method $this setStatus(string $value)
 * @method $this set_Messages(Model\_Message[] $value)
 * @method $this unsetBackupCodes()
 * @method $this unsetCountryCode()
 * @method $this unsetIsPhoneConfirmed()
 * @method $this unsetIsTwoFactorEnabled()
 * @method $this unsetMessage()
 * @method $this unsetNationalNumber()
 * @method $this unsetPhoneNumber()
 * @method $this unsetStatus()
 * @method $this unset_Messages()
 */
class AccountSecurityInfoResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'backup_codes'          => '',
        'is_phone_confirmed'    => '',
        'country_code'          => '',
        'phone_number'          => '',
        'is_two_factor_enabled' => '',
        'national_number'       => '',
    ];
}
