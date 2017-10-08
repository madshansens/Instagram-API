<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SendConfirmEmailResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'title'          => '',
        'is_email_legit' => '',
        'body'           => '',
    ];
}
