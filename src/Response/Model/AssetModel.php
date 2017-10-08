<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

class AssetModel extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'asset_url' => '',
        'id'        => 'string',
    ];
}
