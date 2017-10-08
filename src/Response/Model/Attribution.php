<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * @method mixed getName()
 * @method bool isName()
 * @method $this setName(mixed $value)
 * @method $this unsetName()
 */
class Attribution extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'name' => '',
    ];
}
