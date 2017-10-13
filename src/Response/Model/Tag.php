<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Tag.
 *
 * @method string getId()
 * @method mixed getMediaCount()
 * @method mixed getName()
 * @method bool isId()
 * @method bool isMediaCount()
 * @method bool isName()
 * @method $this setId(string $value)
 * @method $this setMediaCount(mixed $value)
 * @method $this setName(mixed $value)
 * @method $this unsetId()
 * @method $this unsetMediaCount()
 * @method $this unsetName()
 */
class Tag extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'media_count' => '',
        'name'        => '',
        'id'          => 'string',
    ];
}
