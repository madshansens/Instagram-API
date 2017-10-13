<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * BusinessPageInfo.
 *
 * @method mixed getHasNextPage()
 * @method mixed getHasPreviousPage()
 * @method bool isHasNextPage()
 * @method bool isHasPreviousPage()
 * @method $this setHasNextPage(mixed $value)
 * @method $this setHasPreviousPage(mixed $value)
 * @method $this unsetHasNextPage()
 * @method $this unsetHasPreviousPage()
 */
class BusinessPageInfo extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'has_next_page'     => '',
        'has_previous_page' => '',
    ];
}
