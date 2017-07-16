<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getHashtag()
 * @method mixed getPlace()
 * @method mixed getUser()
 * @method bool isHashtag()
 * @method bool isPlace()
 * @method bool isUser()
 * @method setHashtag(mixed $value)
 * @method setPlace(mixed $value)
 * @method setUser(mixed $value)
 */
class HiddenEntities extends AutoPropertyHandler
{
    public $user;
    public $hashtag;
    public $place;
}
