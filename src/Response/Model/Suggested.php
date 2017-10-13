<?php

namespace InstagramAPI\Response\Model;

use InstagramAPI\AutoPropertyMapper;

/**
 * Suggested.
 *
 * @method mixed getClientTime()
 * @method mixed getPosition()
 * @method User getUser()
 * @method bool isClientTime()
 * @method bool isPosition()
 * @method bool isUser()
 * @method $this setClientTime(mixed $value)
 * @method $this setPosition(mixed $value)
 * @method $this setUser(User $value)
 * @method $this unsetClientTime()
 * @method $this unsetPosition()
 * @method $this unsetUser()
 */
class Suggested extends AutoPropertyMapper
{
    const JSON_PROPERTY_MAP = [
        'position'    => '',
        'user'        => 'User',
        'client_time' => '',
    ];
}
