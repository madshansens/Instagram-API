<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class ReelsMediaResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'reels_media' => 'Model\Reel[]',
        'reels'       => 'Model\Reel[]',
    ];
}
