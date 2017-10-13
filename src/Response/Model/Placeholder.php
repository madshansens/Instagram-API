<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Placeholder.
 *
 * @method mixed getIsLinked()
 * @method mixed getMessage()
 * @method mixed getTitle()
 * @method bool isIsLinked()
 * @method bool isMessage()
 * @method bool isTitle()
 * @method $this setIsLinked(mixed $value)
 * @method $this setMessage(mixed $value)
 * @method $this setTitle(mixed $value)
 * @method $this unsetIsLinked()
 * @method $this unsetMessage()
 * @method $this unsetTitle()
 */
class Placeholder extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'is_linked' => '',
        'title'     => '',
        'message'   => '',
    ];
}
