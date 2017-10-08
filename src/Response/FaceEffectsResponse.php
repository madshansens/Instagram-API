<?php

namespace InstagramAPI\Response;

use InstagramAPI\Response;

class FaceEffectsResponse extends Response
{
    const JSON_PROPERTY_MAP = [
        'sdk_version'    => '',
        'effects'        => 'Model\Effect[]',
        'loading_effect' => 'Model\Effect',
    ];
}
