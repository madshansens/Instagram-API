<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class Challenge extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'url'                 => '',
        'api_path'            => '',
        'hide_webview_header' => '',
        'lock'                => '',
        'logout'              => '',
        'native_flow'         => '',
    ];
}
