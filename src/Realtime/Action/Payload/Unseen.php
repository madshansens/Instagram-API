<?php

namespace InstagramAPI\Realtime\Action\Payload;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getCount()
 * @method mixed getTimestamp()
 * @method bool isCount()
 * @method bool isTimestamp()
 * @method setCount(mixed $value)
 * @method setTimestamp(mixed $value)
 */
class Unseen extends AutoPropertyHandler
{
    public $timestamp;
    public $count;
}
