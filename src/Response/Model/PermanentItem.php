<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method string getItemId()
 * @method mixed getItemType()
 * @method mixed getText()
 * @method mixed getTimestamp()
 * @method string getUserId()
 * @method bool isItemId()
 * @method bool isItemType()
 * @method bool isText()
 * @method bool isTimestamp()
 * @method bool isUserId()
 * @method setItemId(string $value)
 * @method setItemType(mixed $value)
 * @method setText(mixed $value)
 * @method setTimestamp(mixed $value)
 * @method setUserId(string $value)
 */
class PermanentItem extends AutoPropertyHandler
{
    /**
     * @var string
     */
    public $item_id;
    /**
     * @var string
     */
    public $user_id;
    public $timestamp;
    public $item_type;
    public $text;
}
