<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Effect.
 *
 * @method mixed getAssetUrl()
 * @method string getEffectFileId()
 * @method string getEffectId()
 * @method string getId()
 * @method mixed getInstructions()
 * @method mixed getThumbnailUrl()
 * @method mixed getTitle()
 * @method bool isAssetUrl()
 * @method bool isEffectFileId()
 * @method bool isEffectId()
 * @method bool isId()
 * @method bool isInstructions()
 * @method bool isThumbnailUrl()
 * @method bool isTitle()
 * @method $this setAssetUrl(mixed $value)
 * @method $this setEffectFileId(string $value)
 * @method $this setEffectId(string $value)
 * @method $this setId(string $value)
 * @method $this setInstructions(mixed $value)
 * @method $this setThumbnailUrl(mixed $value)
 * @method $this setTitle(mixed $value)
 * @method $this unsetAssetUrl()
 * @method $this unsetEffectFileId()
 * @method $this unsetEffectId()
 * @method $this unsetId()
 * @method $this unsetInstructions()
 * @method $this unsetThumbnailUrl()
 * @method $this unsetTitle()
 */
class Effect extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'title'          => '',
        'id'             => 'string',
        'effect_id'      => 'string',
        'effect_file_id' => 'string',
        'asset_url'      => '',
        'thumbnail_url'  => '',
        'instructions'   => '',
    ];
}
