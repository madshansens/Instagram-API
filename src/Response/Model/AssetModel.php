<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * AssetModel.
 *
 * @method mixed getAssetUrl()
 * @method string getId()
 * @method bool isAssetUrl()
 * @method bool isId()
 * @method $this setAssetUrl(mixed $value)
 * @method $this setId(string $value)
 * @method $this unsetAssetUrl()
 * @method $this unsetId()
 */
class AssetModel extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'asset_url' => '',
        'id'        => 'string',
    ];
}
