<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * @method Item[] getItems()
 * @method mixed getMoreAvailable()
 * @method bool isItems()
 * @method bool isMoreAvailable()
 * @method $this setItems(Item[] $value)
 * @method $this setMoreAvailable(mixed $value)
 * @method $this unsetItems()
 * @method $this unsetMoreAvailable()
 */
class Aymf extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'items'          => 'Item[]',
        'more_available' => '',
    ];
}
