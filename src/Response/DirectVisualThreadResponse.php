<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class DirectVisualThreadResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        Model\DirectThread::class, // Import property map.
    ];
}
