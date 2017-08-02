<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getExpiringAt()
 * @method string getId()
 * @method mixed getImage()
 * @method User getUser()
 * @method bool isExpiringAt()
 * @method bool isId()
 * @method bool isImage()
 * @method bool isUser()
 * @method setExpiringAt(mixed $value)
 * @method setId(string $value)
 * @method setImage(mixed $value)
 * @method setUser(User $value)
 */
class Media extends AutoPropertyHandler
{
    public $image;
    /**
     * @var string
     */
    public $id;
    /**
     * @var User
     */
    public $user;
    public $expiring_at;
}
