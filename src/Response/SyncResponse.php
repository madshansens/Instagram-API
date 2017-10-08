<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class SyncResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'experiments' => 'Model\Experiment[]',
    ];
}
