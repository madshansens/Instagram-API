<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getHasNextPage()
 * @method mixed getHasPreviousPage()
 * @method bool isHasNextPage()
 * @method bool isHasPreviousPage()
 * @method setHasNextPage(mixed $value)
 * @method setHasPreviousPage(mixed $value)
 */
class BusinessPageInfo extends AutoPropertyHandler
{
    public $has_next_page;
    public $has_previous_page;
}
