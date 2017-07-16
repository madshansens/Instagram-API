<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyHandler;

/**
 * @method mixed getClientTime()
 * @method mixed getPosition()
 * @method User getUser()
 * @method bool isClientTime()
 * @method bool isPosition()
 * @method bool isUser()
 * @method setClientTime(mixed $value)
 * @method setPosition(mixed $value)
 * @method setUser(User $value)
 */
class Suggested extends AutoPropertyHandler
{
    public $position;
    /**
     * @var User
     */
    public $user;
    public $client_time;
}
