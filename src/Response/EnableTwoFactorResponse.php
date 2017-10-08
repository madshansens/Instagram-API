<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class EnableTwoFactorResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'backup_codes' => '',
    ];
}
