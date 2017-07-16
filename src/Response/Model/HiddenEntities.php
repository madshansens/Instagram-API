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

    // TODO: The server returns each of these fields as [] arrays, but we don't
    // know what kind of objects those arrays can contain since we've never seen
    // any values in them. So for now, these are left as default types. Most
    // likely, they'll need to be User[], Tag[] and Location[].
}
