<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

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
