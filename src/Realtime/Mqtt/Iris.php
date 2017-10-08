<?php

namespace InstagramAPI\Realtime\Mqtt;

use InstagramAPI\AutoPropertyMapper;

class Iris extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'seq_id'        => 'int',
        'succeeded'     => 'bool',
        'error_type'    => 'int',
        'error_message' => 'string',
    ];
}
