<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class AccountCreateResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'account_created' => '',
        'created_user'    => 'Model\User',
    ];
}
