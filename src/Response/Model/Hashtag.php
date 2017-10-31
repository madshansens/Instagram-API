<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Hashtag.
 *
 * @method string getId()
 * @method string getName()
 * @method bool isId()
 * @method bool isName()
 * @method $this setId(string $value)
 * @method $this setName(string $value)
 * @method $this unsetId()
 * @method $this unsetName()
 */
class Hashtag extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'id'   => 'string',
        'name' => 'string',
    ];
}
