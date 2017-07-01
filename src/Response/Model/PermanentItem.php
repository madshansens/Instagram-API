<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getItemId()
 * @method mixed getItemType()
 * @method mixed getText()
 * @method mixed getTimestamp()
 * @method mixed getUserId()
 * @method bool isItemId()
 * @method bool isItemType()
 * @method bool isText()
 * @method bool isTimestamp()
 * @method bool isUserId()
 * @method setItemId(mixed $value)
 * @method setItemType(mixed $value)
 * @method setText(mixed $value)
 * @method setTimestamp(mixed $value)
 * @method setUserId(mixed $value)
 */
class PermanentItem extends AutoPropertyHandler
{
    public $item_id;
    public $user_id;
    public $timestamp;
    public $item_type;
    public $text;
}
